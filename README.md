AUTILEARN
Overview

AUTILEARN is a desktop and Web educational platform developed using JavaFX and symfony as part of the PIDEV – 3rd Year Engineering Program at Esprit School of Engineering during the academic year 2025–2026.

The platform is designed to provide an interactive learning environment for children through educational games, activities, sessions, and progression tracking. The application allows administrators, teachers, and users to manage educational content while offering a modern and engaging user experience.

The system integrates desktop technologies, database management, multimedia resources, authentication, statistics dashboards, and API integrations to create a complete educational ecosystem.

Features
 User Management
Secure authentication system
Role-based access (Admin / User)
User CRUD management
Profile image management
Password encryption
Session handling
 Games Management
Educational games CRUD
Categorization by difficulty and type
Multimedia support (images)
Interactive game interface
Search and filtering system
📝 Activities Management
Educational activities management
Activity categorization
Dynamic content display
Activity tracking
📅 Sessions Management
Session scheduling
Session tracking
User participation management
Calendar integration
📈 Progression Tracking
User progression statistics
Performance monitoring
Dynamic dashboards
Educational analytics
📊 Admin Dashboard
Centralized administration panel
Statistics and monitoring
User management
Games and activities overview
Modern sidebar dashboard interface
Tech Stack
Frontend
JavaFX 22
FXML & SceneBuilder
CSS3 Styling
Backend
Java 17
Maven
MySQL 8
JDBC
Architecture

The application follows an MVC layered architecture.

src/main/
├── java/tn/esprit/
│   ├── controllers/
│   ├── entities/
│   ├── services/
│   ├── utils/
│   └── tests/
└── resources/
    ├── views/
    ├── css/
    └── images/
Main Modules
Module	Description
Users	Authentication and profile management
Games	Educational games management
Activities	Learning activities management
Sessions	Session organization and tracking
Progression	User performance monitoring
Dashboard	Administration and statistics
Key Functionalities
CRUD operations for all modules
Image upload system
Admin sidebar dashboard
JavaFX dynamic navigation
MySQL database integration
Responsive desktop UI
Statistics dashboard
Educational progression system
Academic Context

Developed at Esprit School of Engineering as part of the PIDEV Engineering Project during the academic year 2025–2026.

Getting Started
Prerequisites
Java 17
Maven 3.8+
MySQL 8
