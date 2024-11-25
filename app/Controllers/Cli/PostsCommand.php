<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Cli;

use \Luminova\Base\BaseCommand;
use \Luminova\Command\Utils\Color;
use \Luminova\Command\Utils\Text;
use \Luminova\Command\Utils\Image;
use \Luminova\Attributes\Route;
use \App\Models\Posts;
use \App\Models\Users;
use \JsonException;

class PostsCommand extends BaseCommand 
{
    /**
     * Command group associated with posts.
     * 
     * {@inheritdoc}
     */
    protected string $group = 'posts';

    /**
     * Command name for identifying this CLI tool.
     * 
     * {@inheritdoc}
     */
    protected string $name  = 'post-command';

    /**
     * Example usages for this command.
     * 
     * {@inheritdoc}
     */
    protected array $usages = [
        'php index.php posts --help' => 'Show help information for the posts CLI tool.',
        'php index.php posts list --limit=<value> --offset=<value>' => 'List blog posts with optional limit and offset for pagination.',
        'php index.php posts get --post-id=<value>' => 'Retrieve a specific blog post by its ID.',
        'php index.php posts create --body=<json-> --image=<path||base64>' => 'Create a new blog post. With a JSON string containing "userId", "title" and "body" fields for the new post, e.g., \'{ "userId": 1, "title": "My Post", "body": "Post content" }\'.',
        'php index.php posts update --post-id=<value> --body=<json> --image=<path||base64>' => 'Update an existing blog post. With the post\'s ID and JSON string containing the updated "title" and "body" fields.',
        'php index.php posts delete --post-id=<value>' => 'Delete a blog post by its ID.',
    
        // User management commands
        'php index.php posts key --user-id=<value> --quota=<value> --expiry=<value>' => 'Generate an API key for the user with the specified ID. Set API quota and expiration time (in seconds).',
        'php index.php posts users --limit=<value> --offset=<value>' => 'List users with optional limit and offset for pagination.',
    ];

    /**
     * Command options for customizing behavior.
     * 
     * {@inheritdoc}
     */
    protected array $options = [
        '-h, --help' => "Display the help message for novakit or a specific controller command.",
        '-l, --limit' => "Specify the maximum number of records to display (e.g., limit results to a certain number of posts or users).",
        '-o, --offset' => "Set the starting point for records to display (e.g., skip a specified number of posts or users).",
        '-p, --post-id' => "Specify the ID of a specific post to target in commands like 'get', 'update', or 'delete'.",
        '-b, --body' => "Provide a JSON-formatted string containing the data for creating or updating a post.",
        '-i, --image' => "Provide an image path or base64 encoded image to be uploaded while creating or updating post.",
        
        // User management commands
        '-u, --user-id' => "Specify the user ID for generating an API key.",
        '-q, --quota' => "Set the API quota limit for a user set 0 for unlimited (e.g., specify the maximum usage limit for a user's API key).",
        '-e, --expiry' => "Specify the expiration time for an API key in seconds (default is 2592000 seconds, or 30 days)."
    ];
    
    /**
     * Command description command.
     * 
     * {@inheritdoc}
     */
    protected string $description = 'CLI API client implementation example for managing post on a website.';

    /**
     * Examples of command usage.
     * 
     * {@inheritdoc}
     */
    protected array $examples = [
        'php index.php posts list --limit=10' => 'List posts with pagination support (limit and offset).',
        'php index.php posts get --post-id=2' => 'Retrieve a post by its ID.',
        'php index.php posts delete --post-id=4' => 'Delete a post by its ID.',
        'php index.php posts create --body=\'{userId: 6, title: "New Post", body: "New post Content"}\'' => 'Create a new post with specified title and content.',
        'php index.php posts update --post-id=3 --body=\'{title: "Updated Title", body: "Updated content"}\'' => 'Update an existing post by its ID with new title and content.',

        // User management examples
        'php index.php posts key --user-id=2 --quota=100 --expiry=3600' => 'Generate an API key for user with ID 2, set quota to 100, and expiry to 3600 seconds.',
        'php index.php posts users --limit=3' => 'List users with pagination (limit 10, starting at offset 0).'
    ];

    /**
     * Instance of the Posts model for CRUD operations.
     * 
     * @var Posts|null $posts
     */
    private static ?Posts $posts = null;

    /**
     * Initializes the `Posts` model instance on command creation.
     * 
     * {@inheritdoc}
     */
    protected function onCreate(): void 
    {
        self::$posts ??= new Posts();
    }

    /**
     * Displays help information for post command.
     * 
     * {@inheritdoc}
     */
    public function help(array $helps): int
    {
        return STATUS_ERROR;
    }

    /**
     * Generates an HTTP API key and sets a request quota for the user.
     * Command: `php index.php posts key --user-id=2 --quota=100 --expiry=61200`
     * 
     * @return int Return command status code for exiting.
     */
    #[Route('key', group: 'posts')]
    public function generateUserKey(): int 
    {
        $user_id = escape($this->getAnyOption('user-id', 'u'));
        $expiry = strict($this->getAnyOption('expiry', 'e', 2592000), 'int');
        $quota = (int) strict($this->getAnyOption('quota', 'q', rand(1, 99999)), 'int'); // Assign quota to the key 
        $payload = [
            'maxQuota' => $quota 
        ];

        $auth = $this->app->jwt->encode($payload, $user_id, (int) $expiry);

        if($auth){
            $this->success("API key was successfully generated\nCopy the key to use in http request authentication bearer header token.");
            $this->writeln(sprintf(
                "User Id: %s\nQuota: %d\nAPI Key: %s", 
                $user_id, $quota, $auth
            ));
            return STATUS_SUCCESS;
        }

        $this->success("Failed to generate API key.");
        return STATUS_ERROR;
    }

    /**
     * Lists users with pagination support, displaying basic user information such as ID, name, email, and quota usage.
     * Command: `php index.php posts users --limit=10 --offset=0`
     * 
     * @return int Return command status code for exiting.
     */
    #[Route('users', group: 'posts')]
    public function users(): int 
    {
        $limit = strict($this->getAnyOption('limit', 'l', 10), 'int');
        $offset = strict($this->getAnyOption('offset', 'o', 0), 'int');
        $users = (new Users())->select(null, ['*'], (int) $limit, (int) $offset);

        if(!$users){
            $this->error('No user available');
            return STATUS_ERROR;
        }
        
        $this->writeln(Color::apply('LIST USERS', Text::FONT_BOLD, 'green'));
        $this->newLine();
        
        $list = [];
        foreach($users as $user){
            $list[] = [
                'Id' => $user->user_id, 
                'Usage' => $user->api_usage_quota, 
                'Name' => $user->user_name, 
                'Email' => $user->user_email, 
                'Joined' => $user->created_on, 
                'Accessed' => $user->updated_on
            ];
        }

        $this->writeln($this->table(
            ['Id', 'Usage', 'Name', 'Email', 'Joined', 'Accessed'],
            $list, null,
            'red', 'green'
        ));
        return STATUS_SUCCESS;
    }

    /**
     * Lists posts with optional pagination.
     * 
     * Command usage: `php index.php posts list --limit=2`
     * 
     * @return int Return command status code for exiting.
     */
    #[Route('list', group: 'posts')]
    public function list(): int 
    {
        $limit = strict($this->getAnyOption('limit', 'l', 10), 'int');
        $offset = strict($this->getAnyOption('offset', 'o', 0), 'int');
        $items = self::$posts->select(null, ['*'], (int) $limit, (int) $offset);

        if(!$items){
            $this->error('No post available');
            return STATUS_ERROR;
        }
        
        $this->writeln(Color::apply('LIST POSTS', Text::FONT_BOLD, 'red'));
        $this->newLine();
        
        foreach($items as $item){
            $this->writeln(
                Text::padEnd(Color::style('[' . $item->pid . '] ', 'green'), 40) . $item->post_title
            );
        }

        $this->newLine();
        $this->writeln('To read a post, run: "php index.php posts get --post-id=<n>"');
        return STATUS_SUCCESS;
    }

    /**
     * Retrieves a specific post by ID and display associated image if available.
     * 
     * Command usage: `php index.php posts get --post-id=2`
     * 
     * @return int Return command status code for exiting.
     */
    #[Route('get', group: 'posts')]
    public function read(): int 
    {
        $pid = strict($this->getAnyOption('post-id', 'pid', 0), 'int');
        if(!$pid){
            $this->error("Invalid post id: {$pid}.");
            return STATUS_ERROR;
        }

        $item = self::$posts->find($pid);
        
        if($item){
            $this->writeln(Color::apply($item->post_title, Text::FONT_BOLD, 'red'));
            $this->writeln($item->post_body);
            $this->newLine();

            if($item->post_image){
                $dir = root('/writeable/storages/posts/');
                $this->writeln(Image::draw($dir . $item->post_image, Image::ASCII_CLASSIC, 30, 30));
                $this->newLine();
            }

            $this->writeln(Color::apply('About Post', Text::FONT_BOLD|Text::FONT_UNDERLINE, 'cyan'));
            $this->writeln($this->table(['User Id', 'Post Id', 'Created', 'Updated'],
            [
                [
                    'User Id' => $item->user_id, 
                    'Post Id' => $item->pid, 
                    'Created' => $item->created_on, 
                    'Updated' => $item->updated_on
                ]
            ]));

            return STATUS_SUCCESS;
        }

        $this->error("Post with id: {$pid} not found.");
        return STATUS_ERROR;
    }

    /**
     * Creates a new post with JSON-formatted body data.
     * 
     * Command usage: `php index.php posts create -i=/path/to/image.png --body='{ "userId": 42, "title": "Testing Luminova CLI API client", "body": "Hello world!" }'`
     * 
     * @return int Return command status code for exiting.
     */
    #[Route('create', group: 'posts')]
    public function create(): int 
    {
        $item = $this->getAnyOption('body', 'b');
        $image = $this->getAnyOption('image', 'i');
        if(!$item){
            $this->error('Invalid post body.');
            return STATUS_ERROR;
        }

        setenv('throw.cli.exceptions', 'true');

        try{
            $item = json_decode($item, null, 512, JSON_THROW_ON_ERROR);
            $dir = root('/writeable/storages/posts/');
            $uuid = func()->uuid();
            $newItem = [
                'user_id' => strict($item->userId, 'int'),
                'post_title' => escape($item->title),
                'post_body' => escape($item->body),
                'post_uuid' => $uuid,
            ];

            if($image && ($path = $this->upload($image, $dir)) !== false){
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $newItem['post_image'] = rename($path, $dir . $uuid . '.' . $extension) 
                    ? $uuid . '.' . $extension
                    : basename($path);
            }

            if(self::$posts->insert([$newItem])){
                $this->success('Post was successfully created.');
                return STATUS_SUCCESS;
            }
        }catch(JsonException $e){
            $this->error("Invalid post json body {$e->getMessage()}.");
        }

        $this->error('Unable to create post.');
        return STATUS_SUCCESS;
    }

    /**
     * Updates an existing post by ID with JSON-formatted body data.
     * 
     * Command usage: `php index.php posts update --post-id=6 --body='{"title": "Testing Updated Luminova CLI API client" }'`
     * 
     * @return int Return command status code for exiting.
     */
    #[Route('update', group: 'posts')]
    public function update(): int 
    {
        $item = $this->getAnyOption('body', 'b');
        $pid = strict($this->getAnyOption('post-id', 'pid', 0), 'int');
        $uid = strict($this->getAnyOption('user-id', 'u', 0), 'int');
        $image = $this->getAnyOption('image', 'i');

        if(!$pid && $uid){
            $this->error("Invalid post id: {$pid} or User id: {$uid}.");
            return STATUS_ERROR;
        }

        if(!$item){
            $this->error('Invalid post body.');
            return STATUS_ERROR;
        }

        setenv('throw.cli.exceptions', 'true');

        try{
            $item = json_decode($item, true, 512, JSON_THROW_ON_ERROR);
            $dir = root('/writeable/storages/posts/');
            $count = 0;
            $newItem = [
                'updated_on' => date('Y-m-d H:i:s')
            ];

            if($image && ($path = $this->upload($image, $dir)) !== false){
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $uuid = func()->uuid();
                $count++;
                $newItem['post_image'] = rename($path, $dir . $uuid . '.' . $extension) 
                    ? $uuid . '.' . $extension
                    : basename($path);
            }

            if(isset($item['title'])){
                $count++;
                $newItem['post_title'] = escape($item['title']);
            }

            if(isset($item['body'])){
                $count++;
                $newItem['post_body'] = escape($item['body']);
            }

            if($count > 0 && self::$posts->updatePost($pid, $uid, $newItem)){
                $this->success("Post id: {$pid} was successfully updated.");
                return STATUS_SUCCESS;
            }

            if(!$count){
                $this->writeln("Nothing to update on post id: {$pid}.", 'yellow');
                return STATUS_SUCCESS;
            }
        }catch(JsonException $e){
            $this->error("Invalid post json body {$e->getMessage()}.");
        }

        $this->error("Unable to update post id: {$pid}.");
        return STATUS_ERROR;
    }

    /**
     * Deletes a post by its ID.
     * 
     * Command usage: `php index.php posts delete --post-id=6`
     * 
     * @return int Return command status code for exiting.
     */
    #[Route('delete', group: 'posts')]
    public function delete(): int 
    {
        $pid = strict($this->getAnyOption('post-id', 'pid', 0), 'int');
        if(!$pid){
            $this->error("Invalid post id: {$pid}.");
            return STATUS_ERROR;
        }

        if(self::$posts->delete($pid)){
            $this->success("Post id: {$pid} was successfully deleted.");
            return STATUS_SUCCESS;
        }

        $this->error("Unable to delete post id: {$pid}.");
        return STATUS_ERROR;
    }

    /**
     * Handles post-related authentication.
     * 
     * @return int Return command status code for exiting.
     */
    #[Route(middleware: 'after', group: 'posts')]
    public function auth(): int 
    {
        return STATUS_SUCCESS;
    }
}