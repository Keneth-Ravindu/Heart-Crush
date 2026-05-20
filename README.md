<h1 align="center">Heart Crush ❤️</h1>
A modern full-stack PHP browser game built using MVC architecture, featuring authentication, persistent game saves, immersive gameplay mechanics, animated UI/UX, boss battle systems, and interactive browser-based puzzle solving.

Heart Crush combines software engineering principles, secure backend development, advanced JavaScript systems, and responsive frontend design to create a polished and engaging gaming experience.

---

# ✨ Features

## 🎮 Core Gameplay

* Puzzle-solving gameplay system
* Dynamic level progression
* Boss battle levels
* Difficulty scaling
* Countdown timer system
* Hint system
* Skip system
* Streak multipliers
* Sudden death mechanics
* Fast-answer bonuses
* Pause & resume functionality
* Persistent game state saving

---

## 👤 User System

* User registration & login
* Secure password hashing
* Remember-me authentication
* Session management
* User profiles
* Avatar customization
* Settings management

---

## 🏆 Leaderboard System

* Persistent score tracking
* Top player leaderboard
* Player statistics
* Best score tracking
* Average score analytics

---

## 🎨 Modern UI/UX

* Fully responsive design
* Glassmorphism UI
* Animated heart particle effects
* Custom animated cursor
* SweetAlert2 modals
* Confetti celebrations
* Dynamic loading screen
* Animated navigation system
* Dynamic audio controls
* Boss-level visual effects

---

## 🔊 Audio & Effects

* Background music system
* Boss battle music
* Sound effects
* Volume controls
* Persistent audio settings

---

## 💾 Persistent Game System

* Database-backed game saves
* Pause-state persistence
* Audio-state persistence
* Progress synchronization

---

# 🛠️ Tech Stack

## Backend

* PHP
* MySQL
* MVC Architecture
* Object-Oriented Programming (OOP)

## Frontend

* HTML5
* CSS3
* JavaScript (ES6)
* Bootstrap 5
* Bootstrap Icons

## Libraries & APIs

* SweetAlert2
* Canvas Confetti
* DiceBear Avatar API
* MarcConrad Puzzle API
* Official Joke API
* Useless Facts API

---

# 🏗️ Software Engineering Principles & Design Patterns

## 🧩 MVC (Model–View–Controller) Architecture

The project follows the MVC architectural pattern to separate responsibilities into independent layers.

### Model Layer

Handles:

* Database interactions
* Business logic
* Game state persistence
* User and score management

Examples:

* `User.php`
* `GameState.php`
* `Score.php`

---

### View Layer

Handles:

* User interface
* Gameplay screens
* Leaderboards
* Profile pages
* Settings pages

Examples:

* `game.php`
* `profile.php`
* `scores.php`

---

### Controller Layer

Handles:

* Request processing
* Authentication
* Session management
* API communication
* Game state synchronization

Examples:

* `loginHandler.php`
* `gameStateHandler.php`
* `updateScores.php`

---

## 🔗 Low Coupling & High Cohesion

The system was designed using low coupling and high cohesion principles.

### High Cohesion

Each module has a focused responsibility.

Examples:

* `User.php` handles user-related logic only
* `Score.php` handles leaderboard functionality
* `game.js` manages gameplay systems

Benefits:

* Easier maintenance
* Better readability
* Improved testing
* Reusable modules

---

### Low Coupling

Modules communicate with minimal dependencies.

Examples:

* Views do not directly access the database
* Controllers communicate with models
* Audio systems are isolated from gameplay logic
* CSS modules are separated by responsibility

Benefits:

* Easier scalability
* Simplified debugging
* Reduced side effects
* Better modularity

---

## ⚡ Event-Driven Programming

The frontend uses event-driven programming extensively.

Implemented using:

* `addEventListener()`
* Keyboard events
* Mouse interactions
* Timer events
* Button click handlers
* Dynamic animations
* Audio triggers

Examples:

* Pause/resume systems
* Hint systems
* Real-time score updates
* Dynamic game interactions

Benefits:

* Interactive gameplay
* Responsive user experience
* Real-time UI updates

---

## 🌐 Interoperability

The system integrates multiple external APIs and third-party libraries.

### Integrated APIs

* MarcConrad Puzzle API
* DiceBear Avatar API
* Official Joke API
* Useless Facts API

### Frontend Libraries

* Bootstrap 5
* SweetAlert2
* Canvas Confetti

Benefits:

* Enhanced functionality
* Faster development
* Extensible architecture
* Better user experience

---

## 🪪 Virtual Identity

The system supports virtual identity management through:

* User authentication
* Personalized profiles
* Avatar customization
* Persistent game progress
* Personalized statistics
* Leaderboard rankings

Users maintain a unique digital identity within the system.

---

## 🧠 Object-Oriented Programming (OOP)

The backend was developed using object-oriented programming principles.

Implemented concepts:

* Classes and objects
* Encapsulation
* Reusability
* Modular architecture
* Singleton design pattern

Benefits:

* Cleaner architecture
* Improved scalability
* Easier maintenance
* Reusable code

---

## 🗃️ Singleton Design Pattern

The Singleton pattern is used for database connection management.

Example:

* `Database::getInstance()`

Benefits:

* Prevents multiple database connections
* Centralized resource management
* Improved performance

---

## 🔄 Modular Design Pattern

The project uses modular frontend and backend systems.

Examples:

* Reusable navbar component
* Independent gameplay engine
* Modular CSS files
* Separate authentication handlers
* Dedicated audio systems

Benefits:

* Better maintainability
* Easier feature expansion
* Improved scalability

---

## 🔐 Secure Authentication Design

Authentication systems were implemented using secure design practices.

Implemented security features:

* Password hashing
* Password verification
* Session regeneration
* Remember-token validation
* Prepared SQL statements
* Input sanitization

Benefits:

* Improved security
* Reduced vulnerabilities
* Secure session handling

---

## 🎮 Game Engine Architecture

The gameplay engine uses modular state-driven architecture.

Features include:

* Persistent state management
* Timer systems
* Boss battle systems
* Dynamic difficulty scaling
* Audio synchronization
* Real-time UI updates

Implemented in:

* `game.js` 

---

## 🎨 Responsive & Component-Based UI Design

The UI uses reusable and responsive components.

Examples:

* Reusable navbar
* Modular CSS systems
* Responsive layouts
* Reusable SweetAlert modals
* Animated leaderboard cards

Benefits:

* Consistent user experience
* Mobile responsiveness
* Easier UI maintenance
* Better scalability

---

# 🏛️ Project Architecture

```plaintext id="vjlwm8"
Heart-Crush/
│
├── Controller/
│   ├── config.php
│   ├── loginHandler.php
│   ├── registerHandler.php
│   ├── logout.php
│   ├── gameStateHandler.php
│   ├── updateScores.php
│   ├── userSettingsHandler.php
│   └── narutoQuotes.php
│
├── Model/
│   ├── User.php
│   ├── GameState.php
│   ├── Score.php
│   └── Database/
│       └── heartCrush.sql
│
├── View/
│   ├── index.php
│   ├── game.php
│   ├── profile.php
│   ├── scores.php
│   ├── settings.php
│   ├── login.php
│   ├── register.php
│   ├── loading.php
│   └── navbar.php
│
├── Assets/
│   ├── css/
│   ├── js/
│   └── audio/
│
├── README.md
└── .gitignore
```

---

# 🎨 UI/UX Highlights

Custom UI systems include:

* Animated glassmorphism cards
* Interactive custom cursor
* Floating particle effects
* Animated leaderboard
* Responsive profile dashboard
* Dynamic navbar system
* Immersive boss-level effects

Implemented in:

* `style.css` 
* `game.css` 
* `cursor.css` 
* `mainStyle.css` 
* `navbar.css` 

---

# 🧠 JavaScript Systems

Advanced frontend systems include:

* Persistent game engine
* Audio engine
* Avatar preview system
* Loading animation system
* Custom cursor system
* Dynamic SweetAlert handling

Implemented in:

* `game.js` 
* `bgaudio.js`
* `loading.js` 
* `cursor.js` 
* `authAlerts.js`

---

# 🗄️ Database Setup

## 1. Create Database

```sql id="kcn9ii"
CREATE DATABASE heartcrush;
```

---

## 2. Import SQL File

Import:

```plaintext id="k6x3ux"
Model/Database/heartCrush.sql
```

using phpMyAdmin or MySQL CLI.

---

## 3. Configure Database Credentials

Inside:

```plaintext id="0um9ju"
Controller/config.php
```

Update:

```php id="vgozsi"
private $host = "localhost";
private $db_name = "heartcrush";
private $username = "root";
private $password = "";
```

---

# 🚀 Installation Guide

## 1. Clone Repository

```bash id="w96w54"
git clone https://github.com/yourusername/Heart-Crush.git
```

---

## 2. Move Project to Server Directory

### XAMPP

```plaintext id="cl1lbf"
htdocs/
```

### WAMP

```plaintext id="r5fjlwm"
www/
```

---

## 3. Import Database

Import:

```plaintext id="wux6g4"
heartCrush.sql
```

using phpMyAdmin.

---

## 4. Configure Database

Update credentials inside:

```plaintext id="h7iowt"
Controller/config.php
```

---

## 5. Run Project

Open in browser:

```plaintext id="e89h0t"
http://localhost/Heart-Crush/View/loading.php
```

---

# 📸 Screenshots

<img width="1920" height="908" alt="Login" src="https://github.com/user-attachments/assets/3ee44eff-f9df-4b97-9e42-bb8a4088bee4" />
<img width="1920" height="910" alt="Register" src="https://github.com/user-attachments/assets/43b9c1fe-2a80-48f7-b7a2-7a9f89f875a7" />
<img width="1920" height="905" alt="Landing" src="https://github.com/user-attachments/assets/de53f508-ec78-4e55-ae46-b56c18af6288" />
<img width="1920" height="909" alt="Landing2" src="https://github.com/user-attachments/assets/f19d8822-c8de-4915-b120-26cef14fffcf" />
<img width="1920" height="910" alt="Game" src="https://github.com/user-attachments/assets/e1b357b1-0efc-4526-b754-3632c50515ed" />
<img width="1920" height="908" alt="PauseScreen" src="https://github.com/user-attachments/assets/23932fdd-7a3f-4d1e-850c-de03cc678e37" />
<img width="1920" height="901" alt="GameOver" src="https://github.com/user-attachments/assets/1937f588-f078-4e88-8658-6557c6a64e68" />
<img width="1920" height="911" alt="LeaderBoard" src="https://github.com/user-attachments/assets/a5f63fcb-6552-414f-9ba0-b0c1bf7bb93a" />
<img width="1920" height="910" alt="Settings" src="https://github.com/user-attachments/assets/cbec57b9-7667-4bf6-9eec-fe15cd15f5ef" />
<img width="1920" height="909" alt="Profile" src="https://github.com/user-attachments/assets/8595fe01-a291-48b0-97f1-d64fe70dffd2" />


---

# 📚 Learning Outcomes

This project demonstrates:

* MVC architecture
* OOP PHP development
* Full-stack web development
* Event-driven programming
* Persistent state management
* Authentication systems
* Session security
* Responsive UI design
* API interoperability
* Advanced JavaScript systems
* Database integration
* Modular software architecture
* Secure backend engineering

---

# 🔮 Future Improvements

* Multiplayer mode
* Real-time matchmaking
* Achievements system
* Mobile application version
* AI-generated puzzle levels
* Online tournaments
* Social sharing system
* Real-time chat

---

# 👨‍💻 Author

Developed by Keneth
Final Year BSc (Hons) Computer Science Student

---

# 🙏 Acknowledgement

I would like to express my sincere gratitude to everyone who supported and guided me throughout the development of *Heart Crush*.

First and foremost, I would like to thank my lecturers and academic supervisors for their valuable guidance, encouragement, and constructive feedback during the development process. Their support helped shape both the technical and professional aspects of this project.

I would also like to thank the [University of Bedfordshire](https://www.beds.ac.uk/) for providing the knowledge, resources, and learning environment necessary to complete this project successfully.

Special thanks for Marc Conrad for the Heart Game API.

Special appreciation goes to the open-source community and the developers behind the technologies, libraries, and APIs used in this project, including:

* PHP
* MySQL
* Bootstrap 5
* SweetAlert2
* Canvas Confetti
* DiceBear Avatar API
* MarcConrad Puzzle API

Their tools and documentation greatly contributed to the development experience.

Finally, I would like to thank my friends and peers for their encouragement, feedback, and motivation throughout the project journey.

This project not only enhanced my technical skills in full-stack web development and software engineering, but also strengthened my problem-solving, system design, debugging, and UI/UX development abilities.

