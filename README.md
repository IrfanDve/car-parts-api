<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
</p>

# Car Parts API

This is a Laravel-based API for managing car parts, orders, user authentication, and payments. Below are the steps to set up, run, and test the API.

---

## Table of Contents
1. [Installation Steps](#installation-steps)
2. [Running the API](#running-the-api)
3. [Testing the API](#testing-the-api)
4. [API Endpoints](#api-endpoints)

---

## Installation Steps

Follow these steps to set up the project locally:

1. **Clone the Repository**
   ```bash
   git clone https://github.com/IrfanDve/car-parts-api.git
   cd car-parts-api
2. **Install Dependencies**
       Make sure you have Composer installed.
       Run the following command to install PHP dependencies:
   ```bash
   composer install
3. **Set Up Environment File**
       Copy the .env.example file to .env:
   ```bash
   cp .env.example .env
   ##Update the .env file with your database credentials and other environment variables:
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=car_parts_db
   DB_USERNAME=root
   DB_PASSWORD=your_password
4. **Generate Application Key**
   Run the following command to generate a unique application key:
   ```bash
   php artisan key:generate
5. **Run Migrations**
   Migrate the database tables:
   ```bash
   php artisan migrate
## Running the API

1. **Start the Development Server**
   Run the Laravel development server:
   ```bash
   php artisan serve
2. **Access the API**
   Use tools like Postman or cURL to interact with the API.

## Testing the API

1. **Test Endpoints Manuallyr**
    Use Postman or cURL to test the API endpoints. Below are some example requests:
   ### List Car Parts
    ```bash
    curl -X GET http://127.0.0.1:8000/api/car-parts

## API Endpoints
### Car Parts Management

#### List Car Parts

- **GET** `/api/car-parts`
- Query Parameters:
  - `category` (optional): Filter by category.
  - `min_price` (optional): Minimum price.
  - `max_price` (optional): Maximum price.
- Response: Paginated list of car parts.

#### Create Car Part
- **POST** `/api/car-parts`
- Body:
  ```json
  {
    "name": "Engine",
    "category": "engine",
    "price": 300.50,
    "stock_quantity": 10
  }

### Order Management

#### Place an Order
- **POST** `/api/orders`
- Body:
  ```json
  {
    "items": [
      {
        "car_part_id": 1,
        "quantity": 2
      }
    ]
  }

### User Authentication

#### Register a User
- **POST** `/api/register`
- Body:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
  }

### Payment Management

#### Create Payment Link
- **POST** `/api/orders/{order}/create-payment-link`
- Response: Payment link for the order.

#### Validate Payment
- **POST** `/api/orders/{order}/validate-payment`
- Response: Payment details and updated order status.
## 
## Notes for Using Postman
To make API testing easier in Postman, you can set up global variables for base_url and access_token. Here's how: and
1. **Set base_url as a Global Variable:**
    Open Postman and go to the Environments section.
    
    Create a new environment (e.g., Car Parts API).
    
    Add a variable named base_url and set its value to your API's base URL (e.g., http://127.0.0.1:8000/api).
    
    Save the environment and select it for use.
2. **Set access_token as a Global Variable:**
   After logging in or registering a user, copy the access_token from the response.

    Add a variable named access_token in the same environment and paste the token as its value.
    
    Save the environment.
3. **Using the Variables in Requests:**
   For the URL, use {{base_url}} followed by the endpoint (e.g., {{base_url}}/api/car-parts).

   For authentication, add the access_token to the request headers:
   ```bash
   {
      "Authorization": "Bearer {{access_token}}"
    }
This setup will save time and make testing more efficient.
>>>>>>> 248d03b31621a6f17fa892e808f8834039a8fcfe
