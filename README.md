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
