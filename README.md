# PHP Luminova Framework RESTful API Example  

![Luminova Logo](https://github.com/luminovang/luminova/raw/main/docs/logo.svg)  

---

## Introduction  

This repository provides a comprehensive example of building a RESTful API using the Luminova PHP framework, showcasing both HTTP and CLI implementations. The example is designed to help developers understand how to create APIs that support full CRUD operations (Create, Read, Update, Delete) and handle various HTTP methods such as `GET`, `POST`, `PUT`, and `DELETE`. It also supports secure content delivery through the `Luminova\Storages\FileDelivery` class. This feature enables serving images stored in private directories directly via URL access, eliminating the need for creating symbolic links (`symlinks`).

In addition to HTTP-based API endpoints, this example also demonstrates the power of Luminova's command-line tools, making it possible to perform API-related tasks directly via the CLI. Whether you're looking to seed your database, manage user tokens, or perform backend operations, this repository covers it all.  

By following the provided steps, you can quickly set up an API infrastructure, learn best practices for working with Luminova, and explore features like database migrations, API client authentication, and middleware for rate-limiting or token validation.  

This project is ideal for developers aiming to:  
- Build scalable and secure APIs for web and mobile applications.  
- Explore the seamless integration of HTTP and CLI workflows.  
- Learn Luminova's approach to rapid API development and management.  

---

## Source Files Highlight   

Below is an overview of the primary files involved in the API implementation, organized by functionality and purpose. Each file plays a critical role in building a RESTful API using the Luminova PHP framework.

---

### Controllers  

Controllers handle HTTP and CLI requests, providing routes and business logic for the API.  

#### HTTP Controllers  
- **[App\Controllers\Http\RestController](/app/Controllers/Http/RestController.php)**  
  Implements the core API endpoints for CRUD operations. This class handles user authentication, input validation, and database interaction for the `/api/v1/posts` route.  

- **[App\Controllers\Http\Welcome](/app/Controllers/Http/Welcome.php)**  
  Serves as the main page controller, providing access to the API's landing page. It also manages private file delivery via the `Luminova\Storages\FileDelivery` class.  

#### CLI Controller  
- **[App\Controllers\Cli\PostsCommand](/app/Controllers/Cli/PostsCommand.php)**  
  Implements command-line interactions with the API, managing posts and handling API keys for clients.  

---

### Database  

#### Migrations  
Define the database schema for the API's core entities.  
- **[App\Database\Migrations\PostsMigration](/app/Database/Migrations/PostsMigration.php)**  
  Defines the schema for the `posts` table, including fields for `title`, `body`, and relationships with users.  

- **[App\Database\Migrations\UserMigration](/app/Database/Migrations/UserMigration.php)**  
  Defines the schema for the `users` table, managing user authentication and profile data.  

#### Seeders  
Populate the database with initial data.  
- **[App\Database\Seeders\PostsSeeder](/app/Database/Seeders/PostsSeeder.php)**  
  Inserts sample post data for testing and development purposes.  

- **[App\Database\Seeders\UserSeeder](/app/Database/Seeders/UserSeeder.php)**  
  Inserts sample user data, including admin and regular user roles.  

---

### Models  
Models provide a programmatic interface for interacting with the database tables.  
- **[App\Models\Posts](/app/Models/Posts.php)**  
  Represents the `posts` table, including relationships to users and validation logic for creating or updating posts.  

- **[App\Models\Users](/app/Models/Users.php)**  
  Represents the `users` table, managing user-specific operations such as authentication and role assignments.  

---

### Configuration  
- **[App\Config\Apis](/app/Config/Apis.php)**  
  Central configuration file for defining HTTP API-related behaviors, such as `allowCredentials`, `allowOrigins`, and `allowHeaders`.  


---

## Installation  

### Clone the Repository  

Clone the repository using Git or download the files directly from GitHub:  

```bash
cd your-project-path
git clone https://github.com/luminovang/luminova-rest-api-example.git
```  

### Install Dependencies  

Navigate to the project directory and update the Composer dependencies:  

```bash
composer update
```  

---

## Database Configuration  

Configure your MySQL socket path in the `.env` file:  

```env
database.mysql.socket.path = /var/mysql/mysql.sock
```  

---

## Start the Development Server  

Use the Luminova built-in development server or a third-party server such as `XAMPP` or `WAMPP`.  

---

## Manage the Database  

### Run Migrations  

Apply database migrations to create the required tables:  

```bash
php novakit db:migrate
```  

### Seed the Database  

Populate the database with sample data:  

```bash
php novakit db:seed
```  

---

## Create an API Client Account  

Generate an API token for your client:  

**Example:**

```bash
cd public
php index.php posts key --user-id=1 --quota=1000 --expiry=3600
```  

Copy the generated API token for use with API tools like `Postman` or `curl`.  

---

## CURL API HTTP Request Example  

### Method `GET`  

Retrieve all post contents.

```bash
curl --location 'https://localhost/your-project-path/public/api/v1/posts' \
--header 'X-Api-Client-Id: <your-client-id>' \
--header 'Authorization: <your-api-token>'
```  

For more examples and details, refer to the [documentation](https://luminova.ng/docs/0.0.0/introduction/installation) in this repository.

---

### Method `GET` 

Retrieve a single post content by id.

```bash
curl --location 'https://localhost/your-project-path/public/api/v1/posts/<post-id>' \
--header 'X-Api-Client-Id: <your-client-id>' \
--header 'Authorization: <your-api-token>'
```

---

### Method `POST` 

Create a new post with an optional image.

```bash
curl --location 'https://localhost/your-project-path/public/api/v1/posts/create' \
--header 'X-Api-Client-Id: <your-client-id>' \
--header 'Authorization: <your-api-token>' \
--form 'body="{
    \"userId\": 10, 
    \"title\": \"This a test new title\", 
    \"body\": \"New Body content\"
}"' \
--form 'image=@"/Path/To/Images/image.png"'
```

---

### Method `PUT` 

Update a existing post with an optional image.

```bash
curl --location --request PUT 'https://localhost/your-project-path/public/api/v1/posts/update/<post-id>' \
--header 'X-Api-Client-Id: <your-client-id>' \
--header 'Authorization: <your-api-token>' \
--form 'body="{
    \"title\": \"New Update a test new title\", 
    \"body\": \"New Update Body content\"
}"'
```

---

### Method `PUT` 

Delete a existing post.

```bash
curl --location --request DELETE 'https://localhost/your-project-path/public/api/v1/posts/delete/<post-id>' \
--header 'X-Api-Client-Id: <your-client-id>' \
--header 'Authorization: <your-api-token>'
```

---

## API CLI Request Example 

First navigate to `public` directory of your project.

#### Users 

Lists users with optional pagination.

```bash 
php index.php posts users --limit=10 --offset=0
```

#### Posts 

Lists posts with optional pagination.

```bash 
php index.php posts list --limit=2
```

For more, run help command to show all available post commands:

```bash
php index.php posts --help
```
