# Digital Signage Management System

A comprehensive web-based digital signage management system built with PHP and MySQL for managing and displaying digital content across multiple screens.

## 📋 Project Overview 

This project was developed by **Ankit Kumar** as part of a technical assessment for **Agumentik Group of Companies** during their campus recruitment drive. The system provides a complete solution for managing digital signage content, including image and video displays, screen grouping, and real-time synchronization.


## 🚀 Demo  
<a href="https://youtu.be/jHOzEXmhpl4" target="_blank">
  <img src="https://img.shields.io/badge/Watch%20Demo-YouTube-red?style=for-the-badge&logo=youtube" alt="Watch Demo">
</a> 

## ✨ Features

### Core Functionality
- **User Authentication**: Secure login/logout system with session management
- **Dashboard Management**: Centralized control panel for system overview
- **Screen Management**: Create, edit, and manage multiple digital screens
- **Content Upload**: Support for images (JPG, PNG, GIF) and videos (MP4, AVI, MOV, WMV)
- **Group Management**: Organize screens into logical groups for easier management
- **Real-time Synchronization**: Live content updates across all connected screens
- **File Storage Management**: Organized media file handling with size restrictions

### Technical Features
- **Responsive Design**: Mobile-friendly interface using Bootstrap
- **RESTful API**: API endpoints for external integrations
- **File Validation**: Secure file upload with type and size validation
- **Session Security**: Protected routes and user authentication
- **Database Integration**: MySQL database for data persistence
- **Clean Architecture**: Modular code structure with separation of concerns 

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Icons**: Font Awesome
- **Server**: Apache/Nginx compatible

## 📁 Project Structure

```
signage_system/
├── api.php                 # API endpoints
├── dashboard.php           # Main dashboard
├── login.php              # User authentication
├── logout.php             # Session termination
├── screens.php            # Screen management
├── groups.php             # Group management 
├── storage.php            # File storage management
├── sync.php               # Content synchronization
├── assets/ 
│   ├── css/
│   │   └── style.css      # Custom styling
│   └── js/
│       └── script.js      # JavaScript functionality
├── config/
│   ├── config.php         # Application configuration
│   └── database.php       # Database configuration
├── includes/
│   ├── header.php         # Common header
│   ├── footer.php         # Common footer
│   └── functions.php      # Core functions
└── uploads/
    ├── images/            # Image storage
    └── videos/            # Video storage
```
 
## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for dependencies)

### Installation Steps 

1. **Clone/Download the project**
   ```bash
   git clone [https://github.com/ankit-kumarz/Digital-Signage-Management-System_Agumentik]
   cd signage_system
   ```

2. **Database Setup**
   - Create a new MySQL database
   - Import the database schema (if SQL file is provided)
   - Update database credentials in `config/database.php`

3. **Configuration**
   - Update `config/config.php` with your server settings
   - Set appropriate file permissions for the `uploads/` directory
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/images/
   chmod 755 uploads/videos/
   ```

4. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure mod_rewrite is enabled (for Apache)
   - Update `BASE_URL` in `config/config.php`

5. **Default Access**
   - Navigate to your configured URL
   - Use the login system to access the dashboard

## 💻 Usage

### Admin Dashboard
1. **Login**: Access the system through the login page
2. **Dashboard**: View system statistics and quick actions
3. **Screen Management**: Create and configure digital screens
4. **Content Upload**: Upload images and videos for display
5. **Group Organization**: Organize screens into logical groups
6. **Synchronization**: Monitor real-time content updates

### API Endpoints
The system provides RESTful API endpoints for external integrations:
- `GET /api.php?action=screens` - Retrieve all screens
- `POST /api.php?action=upload` - Upload new content
- `GET /api.php?action=sync` - Synchronization data

## 🔧 Configuration Options

### File Upload Settings
```php
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov', 'wmv']);
```

### Security Features
- Input sanitization and validation
- Session-based authentication
- File type restrictions
- SQL injection prevention
- XSS protection

## 🧪 Testing

The system includes built-in validation and error handling:
- File upload validation
- User authentication testing
- Database connection verification
- Content synchronization testing

## 📱 Browser Compatibility

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Internet Explorer 11+ (limited support)

## 🤝 Contributing

This project was developed as part of a technical assessment. For improvements or suggestions:
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📄 License

This project is developed for educational and assessment purposes.

## 👨‍💻 Developer Information

**Developer**: Ankit Kumar[Github: https://github.com/ankit-kumarz]  
**Purpose**: Technical Assessment - Agumentik Group of Companies  
**Development Context**: Campus Recruitment Drive  
**Year**: 2025

## 🔗 Contact

For any queries regarding this project:
- **Developer**: Ankit Kumar
- **Project Type**: Campus Recruitment Assessment
- **Company**: Agumentik Group of Companies

---

### 📝 Notes for Recruiters

This digital signage management system demonstrates:
- **Full-stack development skills** with PHP and MySQL
- **Modern web development practices** with responsive design
- **Security implementation** with proper authentication and validation
- **Clean code architecture** with modular design patterns
- **File handling capabilities** with secure upload mechanisms
- **Database design and management** skills
- **API development** for system integrations

The project showcases practical problem-solving skills and the ability to create production-ready web applications suitable for real-world business environments.
