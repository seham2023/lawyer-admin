<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>د. أحمد محمد السالم - ملف المحامي الشخصي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(to right, #3b82f6, #8b5cf6);
            --secondary-gradient: linear-gradient(to right, #1e40af, #7c3aed);
            --success-gradient: linear-gradient(to right, #16a34a, #1e40af);
            --warning-gradient: linear-gradient(to right, #ca8a04, #d97706);
            --dark-bg: linear-gradient(to bottom right, #111827, #1e1b4b, #581c87);
            --light-bg: linear-gradient(to bottom right, #f9fafb, #ffffff, #f5f3ff);
            --card-dark: rgba(255, 255, 255, 0.1);
            --card-light: rgba(255, 255, 255, 0.9);
            --text-dark: #111827;
            --text-light: #f9fafb;
            --border-dark: rgba(255, 255, 255, 0.2);
            --border-light: rgba(255, 255, 255, 0.2);
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            min-height: 100vh;
            background: var(--light-bg);
            color: var(--text-dark);
            transition: all 0.5s ease;
            position: relative;
            overflow-x: hidden;
        }
        body.dark-mode {
            background: var(--dark-bg);
            color: var(--text-light);
        }
        /* Background Elements */
        .background-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.2;
            animation: float 15s infinite ease-in-out;
        }
        .circle-1 {
            top: 5rem;
            left: 5rem;
            width: 20rem;
            height: 20rem;
            background: #3b82f6;
        }
        .circle-2 {
            bottom: 5rem;
            right: 5rem;
            width: 24rem;
            height: 24rem;
            background: #8b5cf6;
            animation-delay: 2s;
        }
        .circle-3 {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16rem;
            height: 16rem;
            background: #f59e0b;
            animation-delay: 1s;
        }
        .particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.2;
            animation: pulse 2s infinite ease-in-out;
        }
        
        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            background: var(--card-light);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        body.dark-mode .navbar {
            background: var(--card-dark);
            border-color: var(--border-dark);
        }
        .nav-container {
            max-width: 88rem;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }
        .nav-logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nav-logo i {
            color: #3b82f6;
        }
        .nav-links {
            display: flex;
            gap: 1.5rem;
        }
        .nav-link {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            padding: 0.5rem 0;
            position: relative;
            transition: color 0.3s ease;
        }
        body.dark-mode .nav-link {
            color: var(--text-light);
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-gradient);
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        .nav-actions {
            display: flex;
            gap: 1rem;
        }
        .btn {
            padding: 0.75rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            outline: none;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .btn-icon {
            width: 1.5rem;
            height: 1.5rem;
        }
        .btn-dark {
            background: #f3f4f6;
            color: #4b5563;
        }
        body.dark-mode .btn-dark {
            background: rgba(255, 255, 255, 0.1);
            color: #fbbf24;
        }
        .btn-light {
            background: rgba(255, 255, 255, 0.1);
            color: #3b82f6;
        }
        body.dark-mode .btn-light {
            color: white;
        }
        
        /* Main Content */
        .main-content {
            margin-top: 80px; /* Account for fixed navbar */
            padding-bottom: 80px; /* Account for footer */
        }
        .container {
            max-width: 88rem;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        
        /* Cover & Profile Section */
        .profile-card {
            background: var(--card-light);
            backdrop-filter: blur(20px);
            border-radius: 2rem;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: fadeInUp 0.6s ease forwards;
        }
        body.dark-mode .profile-card {
            background: var(--card-dark);
            border-color: var(--border-dark);
        }
        .cover-image {
            height: 16rem;
            background: linear-gradient(to right, #3b82f6, #8b5cf6, #ec4899);
            position: relative;
            overflow: hidden;
        }
        .cover-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.2);
        }
        .cover-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
        }
        .cover-icon {
            width: 5rem;
            height: 5rem;
            margin: 0 auto 1rem;
            opacity: 0.3;
        }
        .cover-title {
            font-size: 2.25rem;
            font-weight: 700;
            opacity: 0.5;
        }
        .profile-info {
            position: relative;
            padding: 2rem;
        }
        .avatar-container {
            position: absolute;
            top: -5rem;
            right: 2rem;
            z-index: 2;
        }
        .avatar {
            width: 10rem;
            height: 10rem;
            border-radius: 1.5rem;
            object-fit: cover;
            border: 0.5rem solid white;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .verified-badge {
            position: absolute;
            bottom: -0.5rem;
            right: -0.5rem;
            width: 3rem;
            height: 3rem;
            background: #3b82f6;
            border: 0.25rem solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .available-indicator {
            position: absolute;
            top: -0.5rem;
            left: -0.5rem;
            width: 2rem;
            height: 2rem;
            background: #16a34a;
            border: 0.25rem solid white;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        .profile-header {
            padding-top: 6rem;
            padding-right: 12rem;
        }
        .profile-name {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            background: linear-gradient(to right, #3b82f6, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .profile-title {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }
        .profile-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .stat-icon {
            width: 1.25rem;
            height: 1.25rem;
        }
        .profile-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .follow-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            outline: none;
            gap: 0.5rem;
        }
        .follow-btn.following {
            background: #ef4444;
            color: white;
        }
        .follow-btn.following:hover {
            background: #dc2626;
        }
        .follow-btn:not(.following) {
            background: var(--primary-gradient);
            color: white;
        }
        .follow-btn:not(.following):hover {
            background: var(--secondary-gradient);
        }
        .followers-count {
            text-align: center;
        }
        .followers-number {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .followers-label {
            font-size: 0.875rem;
            opacity: 0.7;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .stat-card {
            text-align: center;
        }
        .stat-icon-container {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 0.75rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }
        body:not(.dark-mode) .stat-icon-container {
            background: #f3f4f6;
        }
        body.dark-mode .stat-icon-container {
            background: rgba(255, 255, 255, 0.1);
        }
        .stat-icon-container:hover {
            transform: scale(1.1);
        }
        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.7;
        }
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .action-buttons {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border-radius: 1rem;
            font-weight: 700;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            outline: none;
            gap: 0.75rem;
        }
        .action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .action-btn.chat {
            background: linear-gradient(to right, #3b82f6, #2563eb);
        }
        .action-btn.audio {
            background: linear-gradient(to right, #16a34a, #15803d);
        }
        .action-btn.video {
            background: linear-gradient(to right, #7c3aed, #6d28d9);
        }
        
        /* Section Styles */
        .section {
            background: var(--card-light);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease forwards;
        }
        body.dark-mode .section {
            background: var(--card-dark);
            border-color: var(--border-dark);
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-icon {
            width: 1.5rem;
            height: 1.5rem;
        }
        .bio-text {
            font-size: 1.125rem;
            line-height: 1.75;
            opacity: 0.9;
        }
        .specializations-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .specializations-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .specialization-item {
            padding: 1rem;
            border-radius: 1rem;
            text-align: center;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        body:not(.dark-mode) .specialization-item {
            background: #dbeafe;
        }
        body.dark-mode .specialization-item {
            background: rgba(255, 255, 255, 0.1);
        }
        .specialization-item:hover {
            transform: scale(1.05);
        }
        .languages-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .language-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(to right, #dcfce7, #bbf7d0);
            color: #166534;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 700;
            border: 1px solid #bef264;
        }
        .experience-item, .education-item {
            position: relative;
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .timeline-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .experience-content, .education-content {
            flex: 1;
        }
        .item-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .item-subtitle {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .item-period {
            font-size: 0.875rem;
            opacity: 0.7;
            margin-bottom: 0.75rem;
        }
        .item-description {
            opacity: 0.9;
        }
        .timeline-connector {
            position: absolute;
            right: 1.5rem;
            top: 3rem;
            width: 2px;
            height: 4rem;
            background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
        }
        .achievements-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 3rem;
        }
        @media (min-width: 768px) {
            .achievements-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .achievement-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 1rem;
            transition: transform 0.3s ease;
        }
        body:not(.dark-mode) .achievement-item {
            background: #fef3c7;
        }
        body.dark-mode .achievement-item {
            background: rgba(255, 255, 255, 0.1);
        }
        .achievement-item:hover {
            transform: scale(1.02);
        }
        .cases-grid, .testimonials-grid, .publications-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .case-item, .testimonial-item, .publication-item {
            padding: 1.5rem;
            border-radius: 1rem;
            transition: transform 0.3s ease;
        }
        body:not(.dark-mode) .case-item,
        body:not(.dark-mode) .testimonial-item,
        body:not(.dark-mode) .publication-item {
            background: #f3f4f6;
        }
        body.dark-mode .case-item,
        body.dark-mode .testimonial-item,
        body.dark-mode .publication-item {
            background: rgba(255, 255, 255, 0.1);
        }
        .case-item:hover,
        .testimonial-item:hover,
        .publication-item:hover {
            transform: scale(1.02);
        }
        .case-header, .testimonial-header, .publication-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .case-title, .testimonial-name, .publication-title {
            font-size: 1.125rem;
            font-weight: 700;
        }
        .case-type, .testimonial-position, .publication-type {
            font-size: 0.875rem;
            opacity: 0.7;
        }
        .case-value, .publication-details {
            font-size: 1.5rem;
            font-weight: 700;
            color: #16a34a;
            margin-bottom: 0.5rem;
        }
        .case-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 700;
        }
        .case-status.completed {
            background: #dcfce7;
            color: #166534;
        }
        .case-status.ongoing {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .testimonial-content {
            font-size: 1.125rem;
            line-height: 1.75;
            font-style: italic;
            opacity: 0.9;
        }
        .testimonial-rating {
            display: flex;
            gap: 0.25rem;
            margin-top: 0.75rem;
        }
        .rating-star {
            color: #fbbf24;
        }
        .publication-details-container {
            display: flex;
            gap: 1rem;
            margin-top: 0.75rem;
            flex-wrap: wrap;
        }
        .publication-type-badge {
            background: #ede9fe;
            color: #7c3aed;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 700;
        }
        .download-btn {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        body:not(.dark-mode) .download-btn {
            color: #7c3aed;
        }
        body.dark-mode .download-btn {
            color: white;
        }
        .download-btn:hover {
            transform: scale(1.1);
        }
        
        /* Footer */
        .footer {
            background: var(--card-light);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-light);
            padding: 2rem;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
        }
        body.dark-mode .footer {
            background: var(--card-dark);
            border-color: var(--border-dark);
        }
        .footer-content {
            max-width: 88rem;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .footer-logo {
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .footer-links {
            display: flex;
            gap: 1.5rem;
        }
        .footer-link {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            transition: color 0.3s ease;
        }
        body.dark-mode .footer-link {
            color: var(--text-light);
        }
        .footer-contact {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        @keyframes pulse {
            0% { opacity: 0.2; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.1); }
            100% { opacity: 0.2; transform: scale(1); }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-header {
                padding-top: 6rem;
                padding-right: 0;
            }
            .profile-actions {
                justify-content: center;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .nav-links, .footer-links {
                display: none;
            }
            .main-content {
                margin-top: 70px;
                padding-bottom: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Background Elements -->
    <div class="background-elements">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
        <!-- Particles will be added by JavaScript -->
    </div>
    
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-balance-scale"></i>
                <span>العدالة القانونية</span>
            </div>
            <div class="nav-links">
                <a href="#overview" class="nav-link">نظرة عامة</a>
                <a href="#experience" class="nav-link">الخبرة</a>
                <a href="#education" class="nav-link">التعليم</a>
                <a href="#cases" class="nav-link">القضايا</a>
                <a href="#testimonials" class="nav-link">التوصيات</a>
                <a href="#publications" class="nav-link">المنشورات</a>
            </div>
            <div class="nav-actions">
                <button class="btn btn-dark" id="darkModeToggle">
                    <i class="fas fa-moon btn-icon"></i>
                </button>
                <button class="btn btn-light">
                    <i class="fas fa-share-alt btn-icon"></i>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Cover & Profile Section -->
            <div class="profile-card">
                <!-- Cover Image -->
                <div class="cover-image">
                    <div class="cover-overlay"></div>
                    <div class="cover-content">
                        <i class="fas fa-balance-scale cover-icon"></i>
                        <h2 class="cover-title">العدالة والقانون</h2>
                    </div>
                </div>
                <!-- Profile Info -->
                <div class="profile-info">
                    <!-- Avatar -->
                    <div class="avatar-container">
                        <img src="https://images.pexels.com/photos/5668858/pexels-photo-5668858.jpeg?auto=compress&cs=tinysrgb&w=600" alt="د. أحمد محمد السالم" class="avatar">
                        <div class="verified-badge">
                            <i class="fas fa-check-circle" style="color: white; font-size: 1.25rem;"></i>
                        </div>
                        <div class="available-indicator"></div>
                    </div>
                    <div class="profile-header">
                        <div class="profile-header-content">
                            <div>
                                <h1 class="profile-name">د. أحمد محمد السالم</h1>
                                <p class="profile-title">محامي ومستشار قانوني أول</p>
                                <div class="profile-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-star stat-icon" style="color: #fbbf24;"></i>
                                        <span class="font-bold text-lg">4.9</span>
                                        <span>(347 تقييم)</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-map-marker-alt stat-icon" style="color: #3b82f6;"></i>
                                        <span>الرياض، المملكة العربية السعودية</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-clock stat-icon" style="color: #8b5cf6;"></i>
                                        <span>5 دقائق</span>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-actions">
                                <button class="follow-btn" id="followBtn">
                                    <i class="far fa-heart"></i>
                                    متابعة
                                </button>
                                <div class="followers-count">
                                    <div class="followers-number">1250</div>
                                    <div class="followers-label">متابع</div>
                                </div>
                            </div>
                        </div>
                        <!-- Stats -->
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon-container" style="background: linear-gradient(to right, #dbeafe, #bfdbfe);">
                                    <i class="fas fa-award" style="color: #3b82f6; font-size: 1.5rem;"></i>
                                </div>
                                <div class="stat-value">15</div>
                                <div class="stat-label">سنة خبرة</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon-container" style="background: linear-gradient(to right, #dcfce7, #bbf7d0);">
                                    <i class="fas fa-balance-scale" style="color: #16a34a; font-size: 1.5rem;"></i>
                                </div>
                                <div class="stat-value">450</div>
                                <div class="stat-label">قضية ناجحة</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon-container" style="background: linear-gradient(to right, #fef3c7, #fde68a);">
                                    <i class="fas fa-trophy" style="color: #ca8a04; font-size: 1.5rem;"></i>
                                </div>
                                <div class="stat-value">96%</div>
                                <div class="stat-label">معدل النجاح</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon-container" style="background: linear-gradient(to right, #ede9fe, #ddd6fe);">
                                    <i class="fas fa-clock" style="color: #7c3aed; font-size: 1.5rem;"></i>
                                </div>
                                <div class="stat-value">200</div>
                                <div class="stat-label">ريال/ساعة</div>
                            </div>
                        </div>
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button class="action-btn chat">
                                <i class="fas fa-comments"></i>
                                بدء محادثة
                            </button>
                            <button class="action-btn audio">
                                <i class="fas fa-phone"></i>
                                مكالمة صوتية
                            </button>
                            <button class="action-btn video">
                                <i class="fas fa-video"></i>
                                مكالمة مرئية
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Overview Section -->
            <section id="overview" class="section">
                <h3 class="section-title">
                    <i class="fas fa-user section-icon" style="color: #3b82f6;"></i>
                    نبذة شخصية
                </h3>
                <p class="bio-text">
                    محامي متخصص في القانون التجاري مع خبرة واسعة في قضايا الشركات والعقود التجارية والاستثمار. حاصل على ماجستير في القانون التجاري من جامعة الملك سعود ودكتوراه في القانون الدولي من جامعة السوربون.
                </p>
                <h3 class="section-title" style="margin-top: 2rem;">
                    <i class="fas fa-briefcase section-icon" style="color: #8b5cf6;"></i>
                    التخصصات
                </h3>
                <div class="specializations-grid">
                    <div class="specialization-item">القانون التجاري</div>
                    <div class="specialization-item">قانون الشركات</div>
                    <div class="specialization-item">الاندماج والاستحواذ</div>
                    <div class="specialization-item">القانون المصرفي</div>
                    <div class="specialization-item">قانون الاستثمار</div>
                    <div class="specialization-item">العقود التجارية</div>
                    <div class="specialization-item">التحكيم التجاري</div>
                    <div class="specialization-item">قانون الملكية الفكرية</div>
                </div>
                <h3 class="section-title" style="margin-top: 2rem;">
                    <i class="fas fa-language section-icon" style="color: #16a34a;"></i>
                    اللغات
                </h3>
                <div class="languages-container">
                    <div class="language-item">
                        <i class="fas fa-globe"></i>
                        <span>العربية</span>
                    </div>
                    <div class="language-item">
                        <i class="fas fa-globe"></i>
                        <span>English</span>
                    </div>
                    <div class="language-item">
                        <i class="fas fa-globe"></i>
                        <span>Français</span>
                    </div>
                </div>
            </section>
            
            <!-- Experience Section -->
            <section id="experience" class="section">
                <h3 class="section-title">
                    <i class="fas fa-briefcase section-icon" style="color: #3b82f6;"></i>
                    الخبرة المهنية
                </h3>
                <div class="experience-items">
                    <div class="experience-item">
                        <div class="timeline-icon" style="background: linear-gradient(to right, #3b82f6, #8b5cf6);">
                            <i class="fas fa-briefcase" style="color: white; font-size: 1.25rem;"></i>
                        </div>
                        <div class="experience-content">
                            <h4 class="item-title">شريك أول</h4>
                            <p class="item-subtitle">مكتب السالم والشركاء للمحاماة</p>
                            <p class="item-period">2018 - الآن</p>
                            <p class="item-description">إدارة فريق من 15 محامي متخصص في القانون التجاري والشركات</p>
                        </div>
                        <div class="timeline-connector"></div>
                    </div>
                    <div class="experience-item">
                        <div class="timeline-icon" style="background: linear-gradient(to right, #3b82f6, #8b5cf6);">
                            <i class="fas fa-briefcase" style="color: white; font-size: 1.25rem;"></i>
                        </div>
                        <div class="experience-content">
                            <h4 class="item-title">محامي أول</h4>
                            <p class="item-subtitle">مكتب الخليج للاستشارات القانونية</p>
                            <p class="item-period">2012 - 2018</p>
                            <p class="item-description">تخصص في قضايا الشركات الكبرى والاستثمار الأجنبي</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Education Section -->
            <section id="education" class="section">
                <h3 class="section-title">
                    <i class="fas fa-graduation-cap section-icon" style="color: #8b5cf6;"></i>
                    التعليم والمؤهلات
                </h3>
                <div class="education-items">
                    <div class="education-item">
                        <div class="timeline-icon" style="background: linear-gradient(to right, #8b5cf6, #ec4899);">
                            <i class="fas fa-graduation-cap" style="color: white; font-size: 1.25rem;"></i>
                        </div>
                        <div class="education-content">
                            <h4 class="item-title">دكتوراه في القانون الدولي</h4>
                            <p class="item-subtitle">جامعة السوربون - باريس</p>
                            <div class="item-period">
                                <span>2015</span>
                                <span class="publication-type-badge" style="background: linear-gradient(to right, #fef3c7, #fde68a); color: #ca8a04;">امتياز مع مرتبة الشرف</span>
                            </div>
                        </div>
                        <div class="timeline-connector"></div>
                    </div>
                    <div class="education-item">
                        <div class="timeline-icon" style="background: linear-gradient(to right, #8b5cf6, #ec4899);">
                            <i class="fas fa-graduation-cap" style="color: white; font-size: 1.25rem;"></i>
                        </div>
                        <div class="education-content">
                            <h4 class="item-title">ماجستير القانون التجاري</h4>
                            <p class="item-subtitle">جامعة الملك سعود</p>
                            <div class="item-period">
                                <span>2010</span>
                                <span class="publication-type-badge" style="background: linear-gradient(to right, #fef3c7, #fde68a); color: #ca8a04;">امتياز</span>
                            </div>
                        </div>
                        <div class="timeline-connector"></div>
                    </div>
                    <div class="education-item">
                        <div class="timeline-icon" style="background: linear-gradient(to right, #8b5cf6, #ec4899);">
                            <i class="fas fa-graduation-cap" style="color: white; font-size: 1.25rem;"></i>
                        </div>
                        <div class="education-content">
                            <h4 class="item-title">بكالوريوس الحقوق</h4>
                            <p class="item-subtitle">جامعة الملك عبدالعزيز</p>
                            <div class="item-period">
                                <span>2008</span>
                                <span class="publication-type-badge" style="background: linear-gradient(to right, #fef3c7, #fde68a); color: #ca8a04;">امتياز مع مرتبة الشرف الأولى</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Achievements -->
                <div>
                    <h4 class="section-title">
                        <i class="fas fa-trophy section-icon" style="color: #ca8a04;"></i>
                        الإنجازات والجوائز
                    </h4>
                    <div class="achievements-grid">
                        <div class="achievement-item">
                            <i class="fas fa-trophy" style="color: #ca8a04;"></i>
                            <span>أفضل محامي تجاري في المملكة 2023</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-trophy" style="color: #ca8a04;"></i>
                            <span>عضو نقابة المحامين السعوديين</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-trophy" style="color: #ca8a04;"></i>
                            <span>مستشار قانوني لصندوق الاستثمارات العامة</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-trophy" style="color: #ca8a04;"></i>
                            <span>خبير في الاندماج والاستحواذ</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-trophy" style="color: #ca8a04;"></i>
                            <span>محكم معتمد في غرفة التجارة السعودية</span>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Cases Section -->
            <section id="cases" class="section">
                <h3 class="section-title">
                    <i class="fas fa-balance-scale section-icon" style="color: #16a34a;"></i>
                    القضايا الحديثة
                </h3>
                <div class="cases-grid">
                    <div class="case-item">
                        <div class="case-header">
                            <div>
                                <h4 class="case-title">اندماج شركتين كبريين في قطاع التكنولوجيا</h4>
                                <p class="case-type">اندماج واستحواذ</p>
                            </div>
                            <div class="case-value">500 مليون ريال</div>
                        </div>
                        <div class="case-status completed">مكتملة</div>
                    </div>
                    <div class="case-item">
                        <div class="case-header">
                            <div>
                                <h4 class="case-title">تأسيس صندوق استثماري جديد</h4>
                                <p class="case-type">قانون الاستثمار</p>
                            </div>
                            <div class="case-value">2 مليار ريال</div>
                        </div>
                        <div class="case-status completed">مكتملة</div>
                    </div>
                    <div class="case-item">
                        <div class="case-header">
                            <div>
                                <h4 class="case-title">قضية تحكيم تجاري دولي</h4>
                                <p class="case-type">تحكيم</p>
                            </div>
                            <div class="case-value">300 مليون ريال</div>
                        </div>
                        <div class="case-status ongoing">جارية</div>
                    </div>
                </div>
            </section>
            
            <!-- Testimonials Section -->
            <section id="testimonials" class="section">
                <h3 class="section-title">
                    <i class="fas fa-comment-alt section-icon" style="color: #3b82f6;"></i>
                    آراء العملاء
                </h3>
                <div class="testimonials-grid">
                    <div class="testimonial-item">
                        <div class="testimonial-header">
                            <div class="flex items-center gap-4">
                                <img src="https://images.pexels.com/photos/5668882/pexels-photo-5668882.jpeg?auto=compress&cs=tinysrgb&w=150" alt="م. خالد العتيبي" class="avatar" style="width: 4rem; height: 4rem; border-radius: 1rem;">
                                <div>
                                    <h4 class="testimonial-name">م. خالد العتيبي</h4>
                                    <p class="testimonial-position">الرئيس التنفيذي - شركة التقنية المتقدمة</p>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star rating-star"></i>
                                        <i class="fas fa-star rating-star"></i>
                                        <i class="fas fa-star rating-star"></i>
                                        <i class="fas fa-star rating-star"></i>
                                        <i class="fas fa-star rating-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="testimonial-content">
                            "الدكتور أحمد محامي استثنائي بخبرة عميقة في القانون التجاري. ساعدنا في إتمام عملية اندماج معقدة بنجاح تام."
                        </p>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-header">
                            <div class="flex items-center gap-4">
                                <img src="https://images.pexels.com/photos/5668797/pexels-photo-5668797.jpeg?auto=compress&cs=tinysrgb&w=150" alt="د. فاطمة الحربي" class="avatar" style="width: 4rem; height: 4rem; border-radius: 1rem;">
                                <div>
                                    <h4 class="testimonial-name">د. فاطمة الحربي</h4>
                                    <p class="testimonial-position">مديرة الشؤون القانونية - مجموعة الاستثمار</p>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star rating-star"></i>
                                        <i class="fas fa-star rating-star"></i>
                                        <i class="fas fa-star rating-star"></i>
                                        <i class="fas fa-star rating-star"></i>
                                        <i class="fas fa-star rating-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="testimonial-content">
                            "تعاملت مع الدكتور أحمد في عدة قضايا معقدة. دائماً ما يقدم حلول إبداعية وفعالة."
                        </p>
                    </div>
                </div>
            </section>
            
            <!-- Publications Section -->
            <section id="publications" class="section">
                <h3 class="section-title">
                    <i class="fas fa-book-open section-icon" style="color: #8b5cf6;"></i>
                    المنشورات والأبحاث
                </h3>
                <div class="publications-grid">
                    <div class="publication-item">
                        <div class="publication-header">
                            <div class="flex items-start gap-4">
                                <div class="timeline-icon" style="background: linear-gradient(to right, #8b5cf6, #ec4899);">
                                    <i class="fas fa-book-open" style="color: white; font-size: 1.25rem;"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="publication-title">دليل القانون التجاري في المملكة العربية السعودية</h4>
                                    <div class="publication-details-container">
                                        <span class="publication-type-badge">كتاب</span>
                                        <span>2023</span>
                                        <span>دار النشر القانوني</span>
                                    </div>
                                </div>
                                <button class="download-btn">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="publication-item">
                        <div class="publication-header">
                            <div class="flex items-start gap-4">
                                <div class="timeline-icon" style="background: linear-gradient(to right, #8b5cf6, #ec4899);">
                                    <i class="fas fa-book-open" style="color: white; font-size: 1.25rem;"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="publication-title">التحكيم التجاري الدولي: النظرية والتطبيق</h4>
                                    <div class="publication-details-container">
                                        <span class="publication-type-badge">بحث</span>
                                        <span>2022</span>
                                        <span>مجلة القانون التجاري</span>
                                    </div>
                                </div>
                                <button class="download-btn">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <i class="fas fa-balance-scale"></i>
                <span>العدالة القانونية</span>
            </div>
            <div class="footer-links">
                <a href="#overview" class="footer-link">نظرة عامة</a>
                <a href="#experience" class="footer-link">الخبرة</a>
                <a href="#education" class="footer-link">التعليم</a>
                <a href="#cases" class="footer-link">القضايا</a>
                <a href="#testimonials" class="footer-link">التوصيات</a>
                <a href="#publications" class="footer-link">المنشورات</a>
            </div>
            {{-- <div class="footer-contact">
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+966 11 123 4567</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>info@legaljustice.sa</span>
                </div>
            </div> --}}
        </div>
    </footer>
    
    <script>
        // DOM Elements
        const darkModeToggle = document.getElementById('darkModeToggle');
        const followBtn = document.getElementById('followBtn');
        
        // Initialize dark mode based on system preference
        const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDarkMode) {
            document.body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="fas fa-sun btn-icon"></i>';
        }
        
        // Dark mode toggle
        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            if (document.body.classList.contains('dark-mode')) {
                darkModeToggle.innerHTML = '<i class="fas fa-sun btn-icon"></i>';
            } else {
                darkModeToggle.innerHTML = '<i class="fas fa-moon btn-icon"></i>';
            }
        });
        
        // Follow button toggle
        followBtn.addEventListener('click', () => {
            followBtn.classList.toggle('following');
            if (followBtn.classList.contains('following')) {
                followBtn.innerHTML = '<i class="fas fa-heart"></i> إلغاء المتابعة';
            } else {
                followBtn.innerHTML = '<i class="far fa-heart"></i> متابعة';
            }
        });
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.2)';
                navbar.style.background = 'var(--card-light)';
                if (document.body.classList.contains('dark-mode')) {
                    navbar.style.background = 'var(--card-dark)';
                }
            } else {
                navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
            }
        });
        
        // Create particles for background
        function createParticles() {
            const background = document.querySelector('.background-elements');
            const particleCount = 30;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                // Random properties
                const size = Math.random() * 4 + 1;
                const left = Math.random() * 100;
                const top = Math.random() * 100;
                const delay = Math.random() * 3;
                const duration = Math.random() * 3 + 2;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${left}%`;
                particle.style.top = `${top}%`;
                particle.style.animationDelay = `${delay}s`;
                particle.style.animationDuration = `${duration}s`;
                // Color based on dark mode
                if (document.body.classList.contains('dark-mode')) {
                    particle.style.backgroundColor = 'white';
                } else {
                    particle.style.backgroundColor = '#3b82f6';
                }
                background.appendChild(particle);
            }
        }
        
        // Initialize particles
        createParticles();
    </script>
</body>
</html>