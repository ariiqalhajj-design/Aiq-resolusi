<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMK CIT Manahilul Ilmi - Sekolah Unggulan Masa Depan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary-color: #1a5d1a;
            --secondary-color: #2e8b57;
            --accent-color: #4caf50;
            --light-color: #f1f8f1;
            --dark-color: #0f3d0f;
        }
        
        body {
            background-color: #f9fafc;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Header Styles */
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        header.scrolled {
            padding: 10px 0;
            background-color: rgba(26, 93, 26, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 50px;
            margin-right: 15px;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }
        
        .logo:hover img {
            transform: rotate(5deg);
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        
        .logo-text h1 {
            font-size: 22px;
            line-height: 1.2;
        }
        
        .logo-text span {
            font-size: 14px;
            opacity: 0.9;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin: 0 15px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 5px 0;
            position: relative;
        }
        
        nav ul li a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: white;
            transition: width 0.3s;
        }
        
        nav ul li a:hover:after {
            width: 100%;
        }
        
        .auth-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn-login {
            background-color: white;
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-login:hover {
            background-color: var(--light-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Hero Section with Slideshow */
        .hero {
            position: relative;
            height: 100vh;
            overflow: hidden;
            color: white;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .slideshow {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        
        .slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }
        
        .slide.active {
            opacity: 1;
        }
        
        .slide::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(26, 93, 26, 0.7);
        }
        
        .slide-1 {
            background-image: url('uploads/bersama.jpg');
        }
        
        .slide-2 {
            background-image: url('uploads/latihan.jpg');
        }
        
        .slide-3 {
            background-image: url('uploads/kurban.jpg');
        }
        
        .slide-4 {
            background-image: url('uploads/masjid.jpg');
        }
        
        .hero-content {
            max-width: 800px;
            padding: 20px;
            z-index: 1;
        }
        
        .hero h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            animation: fadeInDown 1s ease;
        }
        
        .hero p {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.9;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 1s ease;
        }
        
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            animation: fadeIn 1.5s ease;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-primary:hover:before {
            left: 100%;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        /* Floating elements */
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            top: 0;
            left: 0;
            z-index: 0;
        }
        
        .floating-element {
            position: absolute;
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }
        
        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            width: 20px;
            height: 20px;
            animation-duration: 20s;
        }
        
        .floating-element:nth-child(2) {
            top: 60%;
            left: 80%;
            width: 40px;
            height: 40px;
            animation-duration: 25s;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            top: 40%;
            left: 70%;
            width: 25px;
            height: 25px;
            animation-duration: 18s;
            animation-delay: 1s;
        }
        
        .floating-element:nth-child(4) {
            top: 70%;
            left: 20%;
            width: 35px;
            height: 35px;
            animation-duration: 22s;
            animation-delay: 3s;
        }
        
        /* Section Styles */
        section {
            padding: 80px 20px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            color: var(--primary-color);
        }
        
        .section-title h2 {
            font-size: 2.2rem;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background: var(--accent-color);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-title p {
            color: #666;
            max-width: 700px;
            margin: 20px auto 0;
        }
        
        .about-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        
        .about-image {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .about-image:before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: 10px;
            bottom: 10px;
            border: 2px solid var(--accent-color);
            border-radius: 10px;
            z-index: -1;
            opacity: 0.5;
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s;
        }
        
        .about-image:hover img {
            transform: scale(1.05);
        }
        
        .about-text h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .about-text p {
            margin-bottom: 15px;
            color: #555;
        }
        
        /* Programs Section */
        .programs {
            background-color: var(--light-color);
        }
        
        .program-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .program-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .program-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 0;
            background: var(--accent-color);
            transition: height 0.3s ease;
        }
        
        .program-card:hover:before {
            height: 100%;
        }
        
        .program-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .program-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .program-card:hover img {
            transform: scale(1.1);
        }
        
        .program-content {
            padding: 25px;
        }
        
        .program-content h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        /* Facilities Section */
        .facility-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .facility-item {
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            height: 250px;
        }
        
        .facility-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .facility-item:hover img {
            transform: scale(1.1);
        }
        
        .facility-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, var(--dark-color));
            padding: 20px;
            color: white;
            transform: translateY(100%);
            transition: transform 0.3s;
        }
        
        .facility-item:hover .facility-overlay {
            transform: translateY(0);
        }
        
        /* News Section */
        .news {
            background-color: var(--light-color);
        }
        
        .news-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .news-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .news-card:hover {
            transform: translateY(-5px);
        }
        
        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .news-content {
            padding: 25px;
        }
        
        .news-date {
            color: var(--accent-color);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .news-content h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        /* Countdown Section */
        .countdown {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: white;
            text-align: center;
            padding: 80px 20px;
        }
        
        .countdown-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .countdown-title {
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        
        .countdown-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .countdown-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            min-width: 100px;
            backdrop-filter: blur(5px);
        }
        
        .countdown-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .countdown-label {
            font-size: 1rem;
            text-transform: uppercase;
        }
        
        /* Testimonials Section */
        .testimonials {
            padding: 80px 20px;
            background-color: white;
        }
        
        .testimonial-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .testimonial-card {
            background: var(--light-color);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        
        .testimonial-card:before {
            content: '"';
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 4rem;
            color: var(--accent-color);
            opacity: 0.2;
            font-family: Georgia, serif;
        }
        
        .testimonial-content {
            margin-bottom: 20px;
            font-style: italic;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .author-details h4 {
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .author-details p {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Contact Section */
        .contact-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        
        .contact-icon {
            background: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            transition: transform 0.3s;
        }
        
        .contact-item:hover .contact-icon {
            transform: rotate(15deg) scale(1.1);
        }
        
        .contact-details h4 {
            font-size: 1.2rem;
            margin-bottom: 5px;
            color: var(--primary-color);
        }
        
        .map {
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
            min-height: 300px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .map iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Footer */
        footer {
            background: var(--dark-color);
            color: white;
            padding: 60px 20px 30px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        
        .footer-column h3 {
            font-size: 1.4rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: var(--accent-color);
        }
        
        .footer-column p {
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .footer-links a:hover {
            opacity: 1;
            color: var(--accent-color);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: background 0.3s, transform 0.3s;
        }
        
        .social-links a:hover {
            background: var(--accent-color);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
        }
        
        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 999;
        }
        
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            background: var(--accent-color);
            transform: translateY(-5px);
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInDown {
            from { 
                opacity: 0;
                transform: translateY(-20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }
        
        /* Mobile Navigation */
        .mobile-nav {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(26, 93, 26, 0.95);
            z-index: 999;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .mobile-nav.active {
            opacity: 1;
            visibility: visible;
        }
        
        .mobile-nav ul {
            list-style: none;
            text-align: center;
        }
        
        .mobile-nav ul li {
            margin: 20px 0;
        }
        
        .mobile-nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .mobile-nav ul li a:hover {
            color: var(--accent-color);
        }
        
        .close-menu {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .about-content,
            .contact-content {
                grid-template-columns: 1fr;
            }
            
            nav ul {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
                font-size: 1.5rem;
                color: white;
                background: none;
                border: none;
                cursor: pointer;
            }
            
            .hero h2 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.2rem;
            }
            
            .countdown-container {
                flex-wrap: wrap;
            }
        }
        
        @media (max-width: 768px) {
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .header-container {
                flex-wrap: wrap;
            }
            
            .auth-buttons {
                margin-top: 15px;
                width: 100%;
                justify-content: center;
            }
            
            .program-cards,
            .news-cards,
            .testimonial-cards {
                grid-template-columns: 1fr;
            }
            
            .countdown-box {
                min-width: 80px;
            }
            
            .countdown-value {
                font-size: 2rem;
            }
        }
        
        .mobile-menu-btn {
            display: none;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="header-container">
            <div class="logo">
                <img src="uploads/cit.webp" alt="Logo Sekolah">
                <div class="logo-text">
                    <h1>SMK CIT Manahilul Ilmi</h1>
                    <span>Sekolah Unggulan Masa Depan</span>
                </div>
            </div>
            
            <nav>
                <ul>
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#about">Tentang</a></li>
                    <li><a href="#programs">Program</a></li>
                    <li><a href="#facilities">Fasilitas</a></li>
                    <li><a href="#news">Berita</a></li>
                    <li><a href="#contact">Kontak</a></li>
                </ul>
            </nav>
            
            <div class="auth-buttons">
                <a href="http://localhost/sekolah/public/login.php" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <button class="close-menu" id="closeMenu">
            <i class="fas fa-times"></i>
        </button>
        <ul>
            <li><a href="#home">Beranda</a></li>
            <li><a href="#about">Tentang</a></li>
            <li><a href="#programs">Program</a></li>
            <li><a href="#facilities">Fasilitas</a></li>
            <li><a href="#news">Berita</a></li>
            <li><a href="#contact">Kontak</a></li>
        </ul>
    </div>

    <!-- Hero Section with Slideshow -->
    <section class="hero" id="home">
        <div class="slideshow">
            <div class="slide slide-1 active"></div>
            <div class="slide slide-2"></div>
            <div class="slide slide-3"></div>
            <div class="slide slide-4"></div>
        </div>
        
        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        
        <div class="hero-content">
            <h2 data-aos="fade-down" data-aos-duration="1000">Selamat Datang di Website SMK CIT Manahilul Ilmi</h2>
            <p data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">Mewujudkan Generasi Muda yang Berkarakter, Kreatif, dan Berdaya Saing Global</p>
            <div class="hero-buttons" data-aos="fade" data-aos-duration="1000" data-aos-delay="400">
                <a href="#programs" class="btn-primary">Program Kami</a>
                <a href="#contact" class="btn-secondary">Hubungi Kami</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about">
        <div class="section-title" data-aos="fade-up">
            <h2>Tentang Sekolah Kami</h2>
            <p>Mengenal lebih dekat SMK CIT Manahilul Ilmi dan visi misi kami</p>
        </div>
        
        <div class="about-content">
            <div class="about-image" data-aos="fade-right">
                <img src="uploads/cit.jpg" alt="Gedung Sekolah">
            </div>
            
            <div class="about-text" data-aos="fade-left" data-aos-delay="200">
                <h3>SMK CIT Manahilul Ilmi</h3>
                <p>SMK CIT Manahilul Ilmi didirikan dengan tujuan untuk mencetak lulusan yang siap kerja dan berdaya saing global. Kami telah menghasilkan banyak alumni yang sukses di berbagai bidang industri.</p>
                <p>Dengan fasilitas yang lengkap dan guru-guru yang berpengalaman, kami berkomitmen untuk memberikan pendidikan terbaik bagi setiap siswa.</p>
                <p>Kurikulum kami dirancang untuk memenuhi kebutuhan industri masa kini dengan fokus pada pengembangan keterampilan teknis dan soft skills.</p>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section class="programs" id="programs">
        <div class="section-title" data-aos="fade-up">
            <h2>Program Keahlian</h2>
            <p>Berbagai program keahlian yang dapat dipilih oleh siswa</p>
        </div>
        
        <div class="program-cards">
            <div class="program-card" data-aos="fade-up" data-aos-delay="0">
                <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Rekayasa Perangkat Lunak">
                <div class="program-content">
                    <h3>Rekayasa Perangkat Lunak</h3>
                    <p>Program keahlian yang mempelajari tentang pengembangan perangkat lunak termasuk pemrograman, basis data, dan pengembangan aplikasi.</p>
                </div>
            </div>
            
            <div class="program-card" data-aos="fade-up" data-aos-delay="200">
                <img src="uploads/pak mail.jpg" alt="Teknik Komputer dan Jaringan">
                <div class="program-content">
                    <h3>  Dinnyah </h3>
                    <p>Ilmu tanpa amal bagaikan pohon tanpa buah, dan amal tanpa ikhlas bagaikan bayang-bayang yang tak terlihat</p>
                </div>
            </div>
            
            <div class="program-card" data-aos="fade-up" data-aos-delay="400">
                <img src="https://images.unsplash.com/photo-1581092580497-e0d23cbdf1dc?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Multimedia">
                <div class="program-content">
                    <h3>  Eanglish</h3>
                    <p>Learn English with patience, because patience is the key to success.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities Section -->
    <section id="facilities">
        <div class="section-title" data-aos="fade-up">
            <h2>Fasilitas Sekolah</h2>
            <p>Fasilitas penunjang kegiatan belajar mengajar yang lengkap dan modern</p>
        </div>
        
        <div class="facility-gallery">
            <div class="facility-item" data-aos="flip-left" data-aos-delay="0">
                <img src="uploads/pos.jpg" alt="Pendopo sekolah">
                <div class="facility-overlay">
                    <h3>Pendopo Sekolah</h3>
                </div>
            </div>
            
            <div class="facility-item" data-aos="flip-left" data-aos-delay="200">
                <img src="uploads/lab.jpg" alt="Laboratorium Komputer">
                <div class="facility-overlay">
                    <h3>Laboratorium Komputer</h3>
                </div>
            </div>
            
            <div class="facility-item" data-aos="flip-left" data-aos-delay="400">
                <img src="uploads/halamn.jpg" alt="Halaman Sekolah">
                <div class="facility-overlay">
                    <h3>Halaman Sekolah</h3>
                </div>
            </div>
            
            <div class="facility-item" data-aos="flip-left" data-aos-delay="600">
                <img src="uploads/lapangan.jpg" alt="Lapangan Olahraga">
                <div class="facility-overlay">
                    <h3>Lapangan Olahraga</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Countdown Section -->
    <section class="countdown">
        <div class="countdown-content" data-aos="fade-up">
            <h2 class="countdown-title">Pendaftaran Murid Baru</h2>
            <p>Segera daftarkan putra/putri Anda untuk tahun ajaran 2023/2024</p>
            
            <div class="countdown-container">
                <div class="countdown-box">
                    <div class="countdown-value" id="days">00</div>
                    <div class="countdown-label">Hari</div>
                </div>
                
                <div class="countdown-box">
                    <div class="countdown-value" id="hours">00</div>
                    <div class="countdown-label">Jam</div>
                </div>
                
                <div class="countdown-box">
                    <div class="countdown-value" id="minutes">00</div>
                    <div class="countdown-label">Menit</div>
                </div>
                
                <div class="countdown-box">
                    <div class="countdown-value" id="seconds">00</div>
                    <div class="countdown-label">Detik</div>
                </div>
            </div>
            
            <a href="#contact" class="btn-primary" style="margin-top: 40px;">Daftar Sekarang</a>
        </div>
    </section>

    <!-- News Section -->
    <section class="news" id="news">
        <div class="section-title" data-aos="fade-up">
            <h2>Berita Terbaru</h2>
            <p>Informasi dan berita terbaru seputar kegiatan sekolah</p>
        </div>
        
        <div class="news-cards">
            <div class="news-card" data-aos="fade-up" data-aos-delay="0">
                <img src="uploads/lomba.jpg" alt="Lomba karate">
                <div class="news-content">
                    <div class="news-date">27 April 2023</div>
                    <h3>Kegiatan perlombaan karate</h3>
                    <p>Ananda Bilal, santri CIT Boarding School Manahilul Ilmi Bogor, berhasil meraih Juara 2 dalam Kejuaraan Kumite Karate yang diselenggarakan di Blu Plaza, Bekasi, pada tanggal 27 April 2025</p>
                </div>
            </div>
            
            <div class="news-card" data-aos="fade-up" data-aos-delay="200">
                <img src="uploads/kurban.jpg" alt="Qurban 1446 H">
                <div class="news-content">
                    <div class="news-date">10 Agustus 2025</div>
                    <h3>Kegiatan Qurban 1446H</h3>
                    <p>Terima kasih kepada seluruh donatur dan relawan. Berkat keikhlasan kita semua, amanah ibadah kurban tahun ini telah tuntas. Semoga setiap kebaikan yang kita bagikan menjadi berkah dan diterima di sisi Allah SWT.</p>
                </div>
            </div>
            
            <div class="news-card" data-aos="fade-up" data-aos-delay="400">
                <img src="uploads/setifikat.jpg" alt="Memperoleh Sertifikat">
                <div class="news-content">
                    <div class="news-date">28 juni 2025</div>
                    <h3>Penerimaan Sertifikat </h3>
                    <p>Kegiatan penerimaan Sertifikat Dan Penggeambilan Foto.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="section-title" data-aos="fade-up">
            <h2>Testimoni Santri</h2>
            <p>Kisah sukses dari lulusan SMK CIT Manahilul Ilmi</p>
        </div>
        
        <div class="testimonial-cards">
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="0">
                <div class="testimonial-content">
                    "Pendidikan di SMK CIT Manahilul Ilmi memberikan saya bekal pengetahuan dan keterampilan yang sangat berguna di dunia kerja. Guru-guru yang profesional dan kurikulum yang relevan dengan industri."
                </div>
                <div class="testimonial-author">
                    <img src="uploads/azzam.png" alt="Alumni 1">
                    <div class="author-details">
                        <h4>Asyraf Azzam</h4>
                        <p>Software Engineer at Tech Company</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-content">
                    "Saya sangat berterima kasih kepada SMK CIT Manahilul Ilmi yang telah membentuk saya menjadi pribadi yang disiplin dan kompeten. Pengalaman magang yang disediakan sekolah sangat membantu karir saya."
                </div>
                <div class="testimonial-author">
                    <img src="uploads/soleh2.jpeg" alt="Alumni 2">
                    <div class="author-details">
                        <h4>Soleh al-ayyubi</h4>
                        <p>Network Specialist at Telecom Company</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="400">
                <div class="testimonial-content">
                    "Metode pembelajaran yang diterapkan di SMK CIT Manahilul Ilmi sangat menyenangkan dan tidak monoton. Saya dibekali dengan keterampilan teknis dan soft skills yang sangat dibutuhkan di dunia kerja."
                </div>
                <div class="testimonial-author">
                    <img src="uploads/brain.jpeg" alt="Alumni 3">
                    <div class="author-details">
                        <h4>Brian Noor amin</h4>
                        <p>Multimedia Designer at Creative Agency</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <div class="section-title" data-aos="fade-up">
            <h2>Kontak Kami</h2>
            <p>Hubungi kami untuk informasi lebih lanjut</p>
        </div>
        
        <div class="contact-content">
            <div class="contact-info" data-aos="fade-right">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Alamat</h4>
                        <p> Karyasari, Leuwiliang, Bogor Regency, West Java 16640</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Telepon</h4>
                        <p>081292598618</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Email</h4>
                        <p>cit.manahilulilmi.or.id</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Jam Operasional</h4>
                        <p>Senin - Jumat: 07.00 - 16.00 WIB</p>
                        <p>Sabtu: 08.00 - 12.00 WIB</p>
                    </div>
                </div>
            </div>
            
            <div class="map" data-aos="fade-left" data-aos-delay="200">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3963.1157328389504!2d106.62080900000001!3d-6.63254579999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69d9e6c22d20af%3A0x2adb55cd0febad98!2sSMK%20TI%20CIT%20BOARDING%20SCHOOL%20MANAHILUL%20ILMI!5e0!3m2!1sen!2sid!4v1756697784798!5m2!1sen!2sid" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>Tentang Kami</h3>
                <p>SMK CIT Manahilul Ilmi adalah sekolah kejuruan terkemuka yang berkomitmen untuk menghasilkan lulusan yang siap kerja dan berdaya saing global.</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/profile.php?id=61573318110224"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/smk.ti.cit.manahilulilmi/"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/@CITTV2023"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Link Cepat</h3>
                <ul class="footer-links">
                    <li><a href="#home"><i class="fas fa-chevron-right"></i> Beranda</a></li>
                    <li><a href="#about"><i class="fas fa-chevron-right"></i> Tentang Kami</a></li>
                    <li><a href="#programs"><i class="fas fa-chevron-right"></i> Program</a></li>
                    <li><a href="#facilities"><i class="fas fa-chevron-right"></i> Fasilitas</a></li>
                    <li><a href="#news"><i class="fas fa-chevron-right"></i> Berita</a></li>
                    <li><a href="#contact"><i class="fas fa-chevron-right"></i> Kontak</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Program Keahlian</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Rekayasa Perangkat Lunak</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Teknik Komputer dan Jaringan</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Multimedia</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Teknik Elektronika</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Akuntansi</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Pemasaran</a></li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            <p>M.Ariiq AL-Hajj All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Slideshow functionality
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.slide');
            let currentSlide = 0;
            
            function nextSlide() {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }
            
            // Change slide every 5 seconds
            setInterval(nextSlide, 5000);
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                if (anchor.getAttribute('href') !== '#') {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            window.scrollTo({
                                top: target.offsetTop - 80,
                                behavior: 'smooth'
                            });
                            
                            // Close mobile menu if open
                            document.getElementById('mobileNav').classList.remove('active');
                        }
                    });
                }
            });
            
            // Mobile menu functionality
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const closeMenuBtn = document.getElementById('closeMenu');
            const mobileNav = document.getElementById('mobileNav');
            
            mobileMenuBtn.addEventListener('click', function() {
                mobileNav.classList.add('active');
            });
            
            closeMenuBtn.addEventListener('click', function() {
                mobileNav.classList.remove('active');
            });
            
            // Header scroll effect
            const header = document.getElementById('header');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
            
            // Back to top button
            const backToTopBtn = document.getElementById('backToTop');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    backToTopBtn.classList.add('visible');
                } else {
                    backToTopBtn.classList.remove('visible');
                }
            });
            
            // Countdown timer
            function updateCountdown() {
                const targetDate = new Date('2023-12-31T23:59:59').getTime();
                const now = new Date().getTime();
                const distance = targetDate - now;
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById('days').textContent = days.toString().padStart(2, '0');
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            }
            
            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>
</body>
</html>