<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Http;

use \Luminova\Base\BaseController;
use \Luminova\Attributes\Prefix;
use \Luminova\Attributes\Route;
use \Luminova\Storages\Uploader;
use \Luminova\Http\File;
use \App\Models\Posts;
use \App\Models\Users;
use \App\Controllers\Errors\ViewErrors;
use \JsonException;
use \stdClass;

#[Prefix(
    pattern: '/api/v1/(:root)', // Make this controller to handle only requests to APIs URL prefix.
    onError: [ViewErrors::class, 'onRestError'] // Set error handler for this controller methods.
)]
class RestController extends BaseController 
{
    /**
     * Default response array for API responses.
     *
     * @var array $response
     */
    private array $response = [
        'status' => 400,
        'message' => 'Bad request was made'
    ];

    /**
     * Handles requests to the API root when no valid endpoint is provided.
     * Renders a "Not Authenticated" response indicating an invalid or missing endpoints.
     * 
     * @return int Return status code.
     */
    #[Route('/api/v1/', methods: ['ANY'])]
    public function index(): int 
    {
        return response(203)->json([
            'status' => 203,
            'message' => 'Not Authenticated'
        ]);
    }

    /**
     * Retrieves a paginated list of posts. Use query parameters `limit` and `offset` 
     * for pagination (e.g., GET `https://example.com/api/v1/posts?limit=10&offset=2`).
     *
     * @return int Return response status code.
     */
    #[Route('/api/v1/posts', methods: ['GET'])]
    public function list(): int 
    {
        if ($this->request->isGet()) {
            $limit = strict($this->request->getGet('limit', 10), 'int');
            $offset = strict($this->request->getGet('offset', 0), 'int');
            $items = (new Posts())->setReturn('array')->select(null, ['*'], $limit, $offset);

            $this->response = [
                'status' => 204,
                'message' => 'No posts found'
            ];

            if ($items) {
                $this->response = [
                    'status' => 200,
                    'items' => []
                ];

                foreach ($items as $item) {
                    $this->response['items'][] = [
                        'id' => $item['pid'],
                        'uuid' => $item['post_uuid'],
                        'userId' => $item['user_id'],
                        'title' => $item['post_title'],
                        'body' => $item['post_title'],
                        'image' =>  $item['post_image'] ? APP_URL . '/cdn/' . $item['post_image'] : null,
                        'link' =>  APP_URL . '/api/v1/posts/' . $item['pid']
                    ];
                }
            }
        }

        return response($this->response['status'])->json($this->response);
    }

    /**
     * Retrieves a specific post by ID from the URL segment (e.g., GET `https://example.com/api/v1/posts/2`).
     *
     * @param int $post_id The ID of the post to retrieve, passed from the URI segment by routing system.
     * 
     * @return int Return response status code.
     */
    #[Route('/api/v1/posts/(:int)', methods: ['GET'])]
    public function read(int $post_id): int 
    {
        $post_id = strict($post_id, 'int');
        $this->response = [
            'status' => 400,
            'message' => "Invalid post ID: {$post_id}."
        ];

        if ($post_id) {
            $item = (new Posts())->setReturn('array')->find($post_id);
            $this->response = [
                'status' => 204,
                'message' => "Post with ID: {$post_id} not found."
            ];

            if ($item) {
                $this->response = [
                    'status' => 200,
                    'item' => [
                        'title' => $item['post_title'],
                        'body' => $item['post_body'],
                        'userId' => $item['user_id'], 
                        'postId' => $item['pid'], 
                        'created_date' => $item['created_on'], 
                        'updated_date' => $item['updated_on']
                    ]
                ];
            }
        }

        return response($this->response['status'])->json($this->response);
    }

    /**
     * Creates a new post with the provided request body (e.g., POST `https://example.com/api/v1/posts/create`).
     *
     * @return int Return response status code.
     */
    #[Route('/api/v1/posts/create', methods: ['POST'])]
    public function create(): int 
    {
        if ($this->request->isPost()) {
            $item = $this->request->getPost('body');
            $this->response = [
                'status' => 400,
                'message' => 'Invalid post body.'
            ];

            if ($item) {
                try {
                    $this->response = [
                        'status' => 406,
                        'message' => 'Unable to create post.'
                    ];

                    $image = $this->request->getFile('image');
                    $newItem = ['post_uuid' => func()->uuid()];

                    if(($image instanceof File) && ($filename = $this->doUpload($image, $newItem['post_uuid'])) !== null){
                        $newItem['post_image'] = $filename;
                    }

                    $item = is_array($item) 
                        ? $item 
                        : json_decode($item, true, 512, JSON_THROW_ON_ERROR);

                    $newItem['user_id'] = strict($item['userId'], 'int');
                    $newItem['post_title'] = escape($item['title']);
                    $newItem['post_body'] = escape($item['body']);

                    $this->rules(true, true, true, false);

                    if ($this->validate->validate($newItem)) {
                        if ((new Posts())->insert([$newItem])) {
                            $this->response = [
                                'status' => 200, //201
                                'message' => 'Post was successfully created.'
                            ];
                        }
                    }else {
                        $this->response['message'] = $this->validate->getErrorLine();
                    }

                } catch (JsonException $e) {
                    $this->jsonError($e->getMessage());
                }
            }
        }

        return response($this->response['status'])->json($this->response);
    }

    /**
     * Updates an existing post by ID in the URI segment (e.g., PUT `https://example.com/api/v1/posts/update/1/100`) 
     * and with the provided PUT body parameters.
     *
     * @param int $post_id The ID of the post to update, passed from the URI segment by routing system.
     * 
     * @return int Return response status code.
     */
    #[Route('/api/v1/posts/update/(:int)', methods: ['PUT'])]
    public function update(int $post_id): int 
    {
        if ($this->request->isMethod('PUT')) {
            $item = $this->request->getPut('body');
            $post_id = (int) strict($post_id, 'int');
            $user_id = (int) strict($this->request->header->get('X-Api-Client-Id', 0), 'int');
            $this->response = [
                'status' => 400,
                'message' => 'Invalid post update parameters.'
            ];

            if ($item && $post_id && $user_id) {
                try {
                    $this->response = [
                        'status' => 204,
                        'message' => "No updates made for post ID: {$post_id}."
                    ];

                    $count = 0;
                    $valid = false;
                    $image = $this->request->getFile('image');
                    $newItem = $this->prepareUpdate($item, $count);

                    // To-Do: Retrieve the post uuid and pass it to `doUpload`
                    // to ensure uniqueness and no duplicates images
                    if(($image instanceof File) && ($filename = $this->doUpload($image)) !== null){
                        $newItem['post_image'] = $filename;
                        $valid = true;
                    }

                    if ($count > 0) {
                        $valid = $this->validate->validate(array_merge($newItem, [
                            'post_id' => $post_id,
                            'user_id' => $user_id
                        ]));

                        if(!$valid){
                            $this->response = [
                                'status' => 406,
                                'message' => $this->validate->getErrorLine()
                            ];
                        }
                    }

                    if ($valid && (new Posts())->updatePost($post_id, $user_id, $newItem)) {
                        $this->response = [
                            'status' => 200,
                            'message' => "Post ID: {$post_id} successfully updated."
                        ];
                    }
                } catch (JsonException $e) {
                    $this->jsonError($e->getMessage());
                }
            }
        }

        return response($this->response['status'])->json($this->response);
    }

    /**
     * Deletes a post by ID from the URI segment (e.g., DELETE `https://example.com/api/v1/posts/delete/1`).
     *
     * @param int $post_id The ID of the post to delete, passed from the URI segment by the routing system.
     * 
     * @return int Return response status code.
     * @todo Implement validation for post_id and user_id.
     */
    #[Route('/api/v1/posts/delete/(:int)', methods: ['DELETE'])]
    public function delete(int $post_id): int 
    {
        if ($this->request->isMethod('DELETE')) {
            $post_id = (int) strict($post_id, 'int');
            $user_id = (int) strict($this->request->header->get('X-Api-Client-Id', 0), 'int');
            $this->response = [
                'status' => 400,
                'message' => "Invalid post Id: {$post_id} or user Id {$user_id}."
            ];

            if ($post_id && $user_id && (new Posts())->deletePost($post_id, $user_id)) {
                $this->response = [
                    'status' => 200,
                    'message' => "Post ID: {$post_id} successfully deleted."
                ];
            } elseif($post_id && $user_id){
                $this->response = [
                    'status' => 406,
                    'message' => "Unable to delete post ID: {$post_id}."
                ];
            }
        }

        return response($this->response['status'])->json($this->response);
    }

    /**
     * Authenticates a user's API request and verifies if their quota allows further usage.
     *
     * This method handles API request middleware authentication by validating a bearer token 
     * and user ID. It can be configured to retrieve authentication details from 
     * request headers, or to use a default demo token for unlimited quota access.
     * 
     * The `authQuota` callback checks if the user has met their usage quota and 
     * updates it if within limits. If authentication and quota checks are successful, 
     * it returns a success status. If authentication fails, it responds with a 
     * 401 Unauthorized status and an error message.
     * 
     * @return int Returns `STATUS_SUCCESS` if authentication and quota are valid; 
     *             `STATUS_ERROR` if unauthorized or quota exceeded.
     * 
     * > To generate new API credentials or show users use the post command.
     * > `php index.php posts key --user-id=2 --quota=100 --expiry=3600`
     */
    #[Route('/api/v1/posts/(:root)', middleware: 'before', methods: ['ANY'])]
    public function auth(): int 
    {
        $message = 'API authentication failed ';
        $valid = $this->app->jwt->validate(
            $this->request->getAuth(), // HTTP_AUTHORIZATION
            $this->request->header->get('X-Api-Client-Id'), // HTTP_X_API_CLIENT_ID
            function(bool $passed, stdClass $payload) use (&$message):  bool
            {
                return $this->authQuota($passed, $payload, $message);
            }
        );

        if($valid){
            return STATUS_SUCCESS;
        }

        // Respond with 401 Unauthorized and error message if authentication fails.
        // Do not return the status code from `response` as it usually returns STATUS_SUCCESS except when rendering failed.
        response(401)->json([
            'status' => 401,
            'message' => $message
        ]);

        return STATUS_ERROR;
    }

    /**
     * Sets a 400 Bad Request response with an error message for invalid JSON input.
     * 
     * @param string $err The error message describing the JSON parsing issue.
     * @return void
     */
    private function jsonError(string $err): void 
    {
        $this->response = [
            'status' => 400,
            'message' => 'Invalid JSON body.'
        ];

        logger('debug', "Invalid JSON body: {$err}.", $this->request->getBody(), true);
    }

    /**
     * Checks if a user has met their API usage quota and updates the quota if not.
     * 
     * This method authenticates a user's API usage quota to ensure they have not 
     * exceeded the maximum allowed requests (`maxQuota`). If the user has not reached 
     * their quota limit, the quota is incremented by one. If the quota has been 
     * exceeded, the method updates the `$message` parameter to include a "quota exceeded" 
     * warning and prevents further processing.
     * 
     * @param bool $passed Indicates if the initial authorization step was successful.
     * @param stdClass $payload The payload object containing user information 
     *                           (`user_id`) and quota limits (`maxQuota`).
     * @param string &$message A reference to a message string to which "quota exceeded" 
     *                         will be appended if the user's quota is met or exceeded.
     * 
     * @return bool Returns `true` if the user's quota is within the allowed limit 
     *              and increments the quota count; returns `false` if the quota is 
     *              exceeded or the authorization fails.
     */
    private function authQuota(bool $passed, stdClass $payload, string &$message): bool 
    {
        if(!$passed){
            $message .= "with error code ({$payload->err->code}). {$payload->err->message}";
            return false;
        }

        if($payload->maxQuota){
            $user = new Users();
            $quota = $user->find($payload->uid, ['api_usage_quota']);

            if(!$quota){
                $message .= "with error code ({$payload->err->code}). {$payload->err->message}";
                return false;
            }

            if($quota->api_usage_quota >= $payload->maxQuota){
                $message .= "with error code ({$payload->err->code}). Quota exceeded limit: {$payload->maxQuota}";
                return false;
            }

            $user->update($payload->uid, [
                'api_usage_quota' => $quota->api_usage_quota + 1,
                'updated_on' => date('Y-m-d H:i:s')
            ]);
        }

        return true;
    }

    /**
     * Uploads an image file to a specified directory and optionally renames it using a UUID.
     *
     * This function handles the upload of an image file, sets configuration for the upload
     * process, and manages file naming based on the provided UUID.
     *
     * @param File $image The File object representing the image to be uploaded.
     * @param string|null $uuid Optional UUID to be used for renaming the file. Default is null.
     *
     * @return string|null Returns the filename of the uploaded image if successful, null otherwise.
     *                     If a UUID is provided and renaming is successful, it returns the UUID-based filename.
     *                     Otherwise, it returns the original filename.
     */
    private function doUpload(File $image, ?string $uuid = null): ?string
    {
        $dir = root('/writeable/storages/posts/');
        $destination = false;
        $image->setConfig([
            'if_existed' => File::IF_EXIST_OVERWRITE,
            'allowed_types' => ['png', 'jpg', 'jpeg', 'webp']
        ]);

        if(Uploader::upload($image, $dir, 0, $destination)){
            $extension = pathinfo($destination, PATHINFO_EXTENSION);
            return ($uuid && rename($destination, $dir . $uuid . '.' . $extension))
                ? $uuid . '.' . $extension
                : basename($destination);
        }

        return null;
    }

    /**
     * Prepares the update data for a post.
     *
     * This function processes the input data for updating a post. It decodes JSON input if necessary,
     * sets up validation rules, and prepares the data for updating the post title and body.
     *
     * @param mixed $item The input data to be processed. Can be an array or a JSON string.
     * @param int &$count A reference to a counter that tracks the number of fields being updated.
     *
     * @return array An array containing the prepared update data.
     *
     * @throws JsonException If JSON decoding fails.
     */
    private function prepareUpdate(mixed $item, int &$count = 0): array 
    {
        $item = is_array($item) 
            ? $item 
            : json_decode($item, true, 512, JSON_THROW_ON_ERROR);
        $newItem = ['updated_on' => date('Y-m-d H:i:s')];

        $this->validate->rules = [
            'post_title' => 'required|string|min(3)|max(255)',
            'user_id' => 'required|int',
            'post_body' => 'required|string',
        ];

        if (isset($item['title'])) {
            $count++;
            $newItem['post_title'] = escape($item['title']);
            $this->rules(true, false, true, true);
        }

        if (isset($item['body'])) {
            $this->rules($count > 0, true, true, true);
            $count++;
            $newItem['post_body'] = escape($item['body']);
        }

        return $newItem;
    }

    /**
     * Sets validation rules for post data.
     *
     * @param bool $title Whether to validate the post title (default: true). 
     * @param bool $body Whether to validate the post body (default: true). 
     * @param bool $user Whether to validate the post user ID (default: false). 
     * @param bool $post Whether to validate the post ID (default: true). 
     * @return void
     */
    private function rules(
        bool $title = true, 
        bool $body = true,
        bool $user = false,
        bool $post = true
    ): void
    {
        $this->validate->rules = [];
        $this->validate->messages = [];

        if($user){
            $this->validate->rules['user_id'] = 'required|integer(positive)';
            $this->validate->messages['user_id'] = [
                'required' => 'Post user Id is required.',
                'integer' => 'Invalid user ID, must be a valid non-negative integer value.',
            ];
        }

        if($post){
            $this->validate->rules['post_id'] = 'required|integer(positive)';
            $this->validate->messages['post_id'] = [
                'required' => 'Post Id is required.',
                'integer' => 'Invalid post ID, must be a valid non-negative integer value.',
            ];
        }

        if($title){
            $this->validate->rules['post_title'] = 'required|string|min(3)|max(255)';
            $this->validate->messages['post_title'] = [
                'required' => 'Post title is required.',
                'string' => 'Post title must be a string.',
                'min' => 'Post title cannot be less than 3 characters.',
                'max' => 'Post title cannot exceed 255 characters.',
            ];
        }

        if($body){
            $this->validate->rules['post_body'] = 'required|string';
            $this->validate->messages['post_body'] = [
                'required' => 'Post content is required.',
                'string' => 'Post content must be a string.',
            ];
        }
    }
}