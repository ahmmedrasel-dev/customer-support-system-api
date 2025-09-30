# Customer Support API

A simple customer support API built with Laravel.

## About the Project

This is a simple API for a customer support system. It allows users to register, login, and create support tickets. There are two types of users: customers and admins. Customers can create and view their own support tickets, while admins can view and manage all support tickets.

## Getting Started

To get started, you will need to have the following installed on your machine:

*   PHP >= 8.1
*   Composer
*   MySQL

### Installation

1.  Clone the repository:

    ```bash
    git clone https://github.com/rasel-ahmmed/customer-support-api.git
    ```

2.  Install the dependencies:

    ```bash
    composer install
    ```

3.  Create a copy of the `.env.example` file and name it `.env`:

    ```bash
    cp .env.example .env
    ```

4.  Generate a new application key:

    ```bash
    php artisan key:generate
    ```

5.  Configure your database in the `.env` file.

6.  Run the database migrations:

    ```bash
    php artisan migrate
    ```

7.  Start the development server:

    ```bash
    php artisan serve
    ```

## API Endpoints

### Authentication

*   `POST /api/register` - Register a new user.
*   `POST /api/login` - Login a user.
*   `POST /api/logout` - Logout a user.

### Tickets

*   `GET /api/tickets` - Get a list of tickets.
*   `POST /api/tickets` - Create a new ticket.
*   `GET /api/tickets/{id}` - Get a single ticket.
*   `PUT /api/tickets/{id}` - Update a ticket.
*   `DELETE /api/tickets/{id}` - Delete a ticket.

### Comments

*   `POST /api/comments` - Create a new comment.

### User Roles

There are two user roles:

*   `customer` - Can create and view their own support tickets.
*   `admin` - Can view and manage all support tickets.

## Contributing

Contributions are welcome! Please feel free to submit a pull request.

## License

This project is licensed under the MIT License.