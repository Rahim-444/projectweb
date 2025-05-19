# Bibliothèque Vintage - E-commerce Platform for Books

## Project Information
- **Developer:** BELKACEMI Abderrahim
- **Student ID:** 222231549109
- **Project Type:** University Project
- **Domain:** E-commerce (Book Store)

## Project Overview
Bibliothèque Vintage is a complete e-commerce web application specialized in selling books. The platform provides both user and administrative interfaces with full functionality for browsing, purchasing, and managing books online. The application implements an Algerian payment method and includes comprehensive order management systems.

## Technologies Used
- **Backend:** PHP
- **Frontend:** HTML5, CSS3, JavaScript
- **Database:** MySQL

## Key Features

### Customer Features
- User registration and authentication system
- Book catalog browsing with filtering by categories
- Advanced search functionality
- Shopping cart management
- Algerian payment methods integration
- Order tracking and history
- User profile management

### Administrator Features
- Book inventory management (add, edit, delete)
- Category management
- Order processing and status updates

### Payment System
- Integration with local Algerian payment methods ( just frontend )
- Support for cash on delivery

## Database Structure
The database consists of multiple interconnected tables:
- Users management (utilisateurs)
- Books catalog (livres)
- Categories management (categories)
- Shopping cart functionality (paniers, articles_panier)
- Order processing (commandes, details_commande)
- Order cancellation tracking (commandes_annulees)

## Installation Instructions

1. **Clone the repository to your local machine or server**
```
git clone https://github.com/Rahim-444/projectweb
```

2. **Database Setup**
- Create a MySQL database
- Import the SQL file provided in `sql.sql`
- Update database connection parameters

3. **Web Server Configuration**
- Configure your web server (Apache/Nginx) to point to the project's root directory
- Ensure PHP 7.4+ is installed with required extensions (mysqli, PDO)


5. **Access the application**
   - Default admin credentials:
     - Email: admin@bibliotheque-vintage.fr
     - Password: admin123 // not sure if it works tho


## Security Features
- Password hashing using Bcrypt
- Form validation and sanitization
- Protection against SQL injection
- CSRF protection
- XSS prevention

## Future Enhancements
- Mobile application development
- Customer review and rating functionality
- Multi-language support (Arabic, French, English)

## License
This project was developed as an educational exercise for university coursework.

## Acknowledgments
Special thanks to the professors and tutors who provided guidance throughout the development of this project.

---
© 2025 BELKACEMI Abderrahim - USTHB
