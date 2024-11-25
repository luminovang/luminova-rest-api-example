# PHP Luminova Framework RESTful API Example  

![Luminova Logo](https://github.com/luminovang/luminova/raw/main/docs/logo.svg)  

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

## API Request Example  

### Using `curl`  

```bash
curl --location 'https://localhost/your-project-path/public/api/v1/posts' \
--header 'X-Api-Client-Id: 1' \
--header 'Authorization: <your-api-token>'
```  

For more examples and details, refer to the [documentation](https://luminova.ng/docs/0.0.0/introduction/installation) in this repository.
