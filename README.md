# PHP Luminova Framework RESTful API Example  

![Luminova Logo](https://github.com/luminovang/luminova/raw/main/docs/logo.svg)  

This example covers both HTTP and CLI API implementations

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
