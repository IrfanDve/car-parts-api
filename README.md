<<<<<<< HEAD
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework">
<img src="https://img.shields.io/p
Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:">

### car-parts-api

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
>>>>>>> 248d03b31621a6f17fa892e808f8834039a8fcfe
