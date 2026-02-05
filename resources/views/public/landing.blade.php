<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Color Tokens ── */
        :root {
            --deep:     #3b1f2b;
            --deep-alt: #4d2a3a;
            --gold:     #c9a96e;
            --gold-hover: #b8955a;
            --cream:    #f5f0eb;
            --card-bg:  #fff8f2;
            --border:   #e2d5c7;
            --border-soft: #d4c4b0;
            --text-light: #f5e6d3;
            --text-muted: #6b5b4f;
            --text-sub:  #d4c4b0;
        }

        /* ── Base ── */
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background: var(--cream);
            color: #2c2c2c;
            text-align: center;
        }

        /* ── Header ── */
        .main-header {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            padding: 20px 40px 20px 60px;
            background-color: var(--deep);
        }

        .logo {
            height: 100px;
            width: auto;
            min-width: 100px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .logo-text {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-light);
            letter-spacing: 2px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
            font-variant: small-caps;
        }

        /* ── Nav ── */
        .nav-container {
            display: flex;
            justify-content: center;
            background: var(--gold);
            padding: 15px;
            flex-wrap: wrap;
            border-bottom: 2px solid var(--gold-hover);
        }

        .nav-container a {
            text-decoration: none;
            color: var(--deep);
            font-size: 15px;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.5px;
            font-family: 'Georgia', serif;
        }

        .nav-container a:hover {
            background: rgba(59,31,43,0.18);
            transform: translateY(-2px);
        }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(rgba(30,15,20,0.55), rgba(30,15,20,0.55)),
                        url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-light);
        }

        .hero-content {
            background: rgba(40,20,28,0.65);
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            border: 1px solid rgba(201,169,110,0.3);
        }

        .hero-content h1 {
            font-size: 36px;
            margin-bottom: 10px;
            color: var(--text-light);
            font-weight: normal;
            letter-spacing: 1px;
        }

        .hero-content p {
            font-size: 17px;
            margin-bottom: 20px;
            color: var(--text-sub);
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        /* ── Quick Plan Form ── */
        .quick-plan {
            background: var(--card-bg);
            border-radius: 6px;
            padding: 40px;
            margin: 60px auto;
            max-width: 1200px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            border: 1px solid var(--border);
        }

        .quick-plan-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group label {
            font-weight: bold;
            color: var(--deep);
            font-size: 14px;
            text-align: left;
            letter-spacing: 0.5px;
        }

        .form-group select {
            padding: 12px;
            border: 1px solid var(--border-soft);
            border-radius: 4px;
            font-size: 15px;
            color: var(--deep);
            background: var(--card-bg);
            transition: border-color 0.3s ease;
            font-family: 'Georgia', serif;
            cursor: pointer;
        }

        .form-group select:focus {
            border-color: var(--gold);
            outline: none;
            box-shadow: 0 0 0 2px rgba(201,169,110,0.2);
        }

        /* ── Buttons ── */
        .primary-button {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: 'Georgia', serif;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            text-decoration: none;
        }

        .primary-button:hover {
            background: var(--gold-hover);
            box-shadow: 0 3px 10px rgba(0,0,0,0.22);
            transform: translateY(-2px);
        }

        .secondary-button {
            background: transparent;
            color: var(--text-light);
            border: 1px solid rgba(201,169,110,0.6);
            padding: 10px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: 'Georgia', serif;
            letter-spacing: 0.5px;
        }

        .secondary-button:hover {
            background: var(--gold);
            color: var(--deep);
            border-color: var(--gold);
            transform: translateY(-2px);
        }

        /* ── Tiles ── */
        .tile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .tile {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--border);
        }

        .tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(59,31,43,0.13);
        }

        .tile h3 {
            color: var(--deep);
            margin-top: 0;
            font-weight: normal;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        .tile p {
            color: var(--text-muted);
        }

        /* ── Section Titles ── */
        .section-title {
            color: var(--deep);
            font-size: 28px;
            margin-bottom: 10px;
            position: relative;
            padding-bottom: 15px;
            font-weight: normal;
            letter-spacing: 1px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: var(--gold);
        }

        .section-subtitle {
            color: var(--text-muted);
            font-size: 16px;
            margin-bottom: 30px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ── Slideshow Section with Diffusing Effect ── */
        .slideshow-section {
            max-width: 1400px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .slideshow-container {
            position: relative;
            width: 100%;
            height: 500px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(59,31,43,0.2);
            border: 2px solid var(--border);
        }

        .slides {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
            animation: none;
        }

        /* Keyframes for diffusing transition */
        @keyframes diffuseIn {
            0% {
                opacity: 0;
                transform: scale(1.05);
                filter: blur(8px);
            }
            15% {
                opacity: 1;
                filter: blur(6px);
            }
            30% {
                filter: blur(4px);
            }
            45% {
                filter: blur(2px);
            }
            60% {
                filter: blur(1px);
            }
            75% {
                filter: blur(0.5px);
                transform: scale(1.02);
            }
            100% {
                opacity: 1;
                transform: scale(1);
                filter: blur(0);
            }
        }

        @keyframes diffuseOut {
            0% {
                opacity: 1;
                transform: scale(1);
                filter: blur(0);
            }
            20% {
                filter: blur(1px);
                transform: scale(1.01);
            }
            40% {
                filter: blur(3px);
                transform: scale(1.02);
            }
            60% {
                filter: blur(5px);
                transform: scale(1.03);
                opacity: 0.8;
            }
            80% {
                filter: blur(7px);
                transform: scale(1.04);
                opacity: 0.4;
            }
            100% {
                opacity: 0;
                transform: scale(1.05);
                filter: blur(8px);
            }
        }

        .slide.active {
            opacity: 1;
            z-index: 2;
            animation: diffuseIn 1.2s ease-out forwards;
        }

        .slide.exiting {
            animation: diffuseOut 1s ease-in forwards;
            z-index: 1;
        }

        .slide-content {
            background: linear-gradient(to top, rgba(59,31,43,0.85) 0%, transparent 100%);
            color: var(--text-light);
            padding: 40px;
            text-align: left;
            width: 100%;
            max-width: 600px;
            animation: contentFadeIn 1.5s ease-out 0.3s both;
        }

        @keyframes contentFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-content h3 {
            font-size: 28px;
            margin: 0 0 10px 0;
            color: var(--text-light);
            font-weight: normal;
            letter-spacing: 0.5px;
        }

        .slide-content p {
            font-size: 16px;
            margin: 0;
            color: var(--text-sub);
            line-height: 1.6;
        }

        .slide-controls {
            position: absolute;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 15px;
            z-index: 10;
        }

        .slide-btn {
            background: rgba(201,169,110,0.8);
            color: var(--deep);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
        }

        .slide-btn:hover {
            background: var(--gold);
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(201,169,110,0.5);
        }

        .slide-indicators {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .indicator:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gold);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.3s ease;
        }

        .indicator.active:after {
            transform: scale(1);
        }

        .indicator.active {
            transform: scale(1.2);
            box-shadow: 0 0 10px rgba(201,169,110,0.5);
        }

        .slide-number {
            position: absolute;
            top: 30px;
            right: 30px;
            background: rgba(59,31,43,0.7);
            color: var(--text-light);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            z-index: 10;
            border: 1px solid rgba(201,169,110,0.3);
            backdrop-filter: blur(5px);
        }

        /* ── Discover Section ── */
        .discover-section {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .discover-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .discover-header .secondary-button {
            color: var(--deep);
            border-color: var(--deep);
        }

        .discover-header .secondary-button:hover {
            background: var(--deep);
            color: var(--text-light);
        }

        /* ── Filter Tags ── */
        .filter-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            justify-content: center;
        }

        .filter-tag {
            padding: 10px 25px;
            background: var(--card-bg);
            border: 1px solid var(--deep);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: var(--deep);
            font-size: 14px;
            font-family: 'Georgia', serif;
        }

        .filter-tag:hover,
        .filter-tag.active {
            background: var(--deep);
            color: var(--text-light);
        }

        /* ── Destination Cards ── */
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .destination-card {
            background: var(--card-bg);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .destination-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 22px rgba(59,31,43,0.15);
        }

        .destination-image {
            height: 180px;
            width: 100%;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }

        .destination-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .destination-content h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: var(--deep);
            font-weight: normal;
            font-size: 19px;
            letter-spacing: 0.5px;
        }

        .destination-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .destination-content p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
            flex-grow: 1;
            margin-top: 0;
        }

        .destination-content .primary-button {
            margin-top: auto;
            width: 100%;
            padding: 10px;
        }

        .price-tag {
            background: var(--gold);
            color: var(--deep);
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 13px;
            letter-spacing: 0.3px;
        }

        .mood-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 15px;
            background: #f5efe8;
            border-radius: 3px;
            font-size: 13px;
            color: var(--deep);
            border: 1px solid var(--border);
        }

        /* ── Explore Categories ── */
        .explore-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .category-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 1px solid var(--border);
            border-top: 3px solid var(--gold);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 22px rgba(59,31,43,0.15);
        }

        .category-card h3 {
            color: var(--deep);
            font-weight: normal;
            font-size: 18px;
        }

        .category-card p {
            color: var(--text-muted);
        }

        .category-icon {
            font-size: 2.5em;
            color: var(--deep);
            margin-bottom: 20px;
        }

        /* ── How It Works ── */
        .how-it-works-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .how-step {
            text-align: center;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            border: 1px solid var(--border);
        }

        .how-step-number {
            background: var(--deep);
            color: var(--text-light);
            width: 60px;
            height: 60px;
            line-height: 60px;
            border-radius: 50%;
            margin: 0 auto 15px;
            font-size: 24px;
            font-weight: normal;
            letter-spacing: 0;
        }

        .how-step h3 {
            color: var(--deep);
            font-weight: normal;
            font-size: 18px;
        }

        .how-step p {
            color: var(--text-muted);
        }

        /* ── AI Features Banner ── */
        .ai-features-banner {
            background: linear-gradient(135deg, var(--deep) 0%, var(--deep-alt) 100%);
            color: var(--text-light);
            padding: 50px 40px;
            border-radius: 6px;
            margin: 60px auto;
            max-width: 1200px;
            box-shadow: 0 8px 28px rgba(59,31,43,0.25);
            border: 1px solid rgba(201,169,110,0.2);
        }

        .ai-features-banner h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--text-light);
            font-weight: normal;
            letter-spacing: 1px;
        }

        .ai-features-banner > div:first-child p {
            font-size: 18px;
            opacity: 0.85;
            max-width: 800px;
            margin: 0 auto;
            color: var(--text-sub);
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 30px;
            text-align: center;
            margin-top: 40px;
        }

        .stat-item {
            flex: 1;
            min-width: 200px;
            color: var(--text-sub);
        }

        .stat-number {
            font-size: 2.8em;
            font-weight: normal;
            margin-bottom: 10px;
            color: var(--gold);
            letter-spacing: 1px;
            transition: transform 0.3s ease;
        }

        /* ── Smart Features Grid ── */
        .smart-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .smart-feature-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            border: 1px solid var(--border);
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .smart-feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(59,31,43,0.13);
        }

        .smart-feature-card .feature-icon {
            font-size: 2em;
            color: var(--deep);
            margin-bottom: 15px;
            display: block;
        }

        .smart-feature-card h3 {
            color: var(--deep);
            font-weight: normal;
            font-size: 18px;
            margin-top: 0;
        }

        .smart-feature-card p {
            color: var(--text-muted);
        }

        /* ── Testimonials ── */
        .testimonials {
            background: #efe8df;
            padding: 60px 20px;
            margin: 60px 0;
            border-top: 1px solid var(--border-soft);
            border-bottom: 1px solid var(--border-soft);
        }

        .testimonial-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .testimonial-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            position: relative;
            border: 1px solid var(--border);
        }

        .testimonial-card:before {
            content: '\201C';
            font-size: 70px;
            color: var(--gold);
            opacity: 0.35;
            position: absolute;
            top: 5px;
            left: 18px;
            font-family: 'Georgia', serif;
            line-height: 1;
        }

        .testimonial-card p {
            color: var(--text-muted);
            line-height: 1.6;
            font-style: italic;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--deep);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-weight: bold;
            font-size: 17px;
            letter-spacing: 1px;
            flex-shrink: 0;
        }

        .user-name {
            font-weight: bold;
            color: var(--deep);
            text-align: left;
        }

        .user-trip {
            color: var(--text-muted);
            font-size: 14px;
            text-align: left;
        }

        /* ── Newsletter ── */
        .newsletter {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 6px;
            text-align: center;
            max-width: 800px;
            margin: 60px auto;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            border: 1px solid var(--border);
        }

        .newsletter-input {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .newsletter-input input {
            flex: 1;
            padding: 15px;
            border: 1px solid var(--deep);
            border-radius: 4px;
            font-size: 16px;
            color: var(--deep);
            background: var(--card-bg);
            font-family: 'Georgia', serif;
        }

        .newsletter-input input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(201,169,110,0.2);
        }

        .newsletter-input button {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 15px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.3s ease;
            font-family: 'Georgia', serif;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .newsletter-input button:hover {
            background: var(--gold-hover);
        }

        .newsletter p.privacy {
            color: #8a7e74;
            font-size: 14px;
            margin-top: 15px;
        }

        /* ── Footer ── */
        .footer {
            background: var(--deep);
            color: var(--text-sub);
            text-align: center;
            padding: 30px 20px;
            margin-top: 40px;
        }

        .footer a {
            color: var(--gold);
            margin: 0 10px;
            transition: color 0.3s ease;
            text-decoration: none;
            font-size: 1.2em;
        }

        .footer a:hover {
            color: var(--text-light);
            transform: scale(1.1);
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 28px; }
            .tile-grid { grid-template-columns: 1fr; }
            .nav-container { flex-direction: column; align-items: center; }
            .discover-header { flex-direction: column; align-items: center; gap: 15px; text-align: center; }
            .filter-tags { justify-content: center; }
            .newsletter-input { flex-direction: column; align-items: center; }
            .newsletter-input input { width: 100%; box-sizing: border-box; }
            .quick-plan-form { grid-template-columns: 1fr; }
            .hero-buttons { flex-direction: column; align-items: center; }
            .main-header { justify-content: center; padding: 15px 20px; }
            .logo { height: 60px; min-width: 60px; }
            .logo-text { font-size: 24px; }
            .nav-container a { font-size: 14px; padding: 8px 10px; }
            .slideshow-container { height: 400px; }
            .slide-content { padding: 20px; }
            .slide-content h3 { font-size: 22px; }
            .slide-btn { width: 40px; height: 40px; font-size: 16px; }
        }

        @media (max-width: 480px) {
            .slideshow-container { height: 350px; }
            .slide-content h3 { font-size: 18px; }
            .slide-content p { font-size: 14px; }
            .slide-controls { bottom: 15px; right: 15px; }
            .slide-number { top: 15px; right: 15px; }
        }
    </style>
</head>
<body>

<!-- Header with YOUR original logo code -->
<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
</header>

<!-- Nav with Flight Booking Added and Register Removed -->
<nav>
    <div class="nav-container">
        <a href="/"><i class="fas fa-home"></i> Home</a>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a> <!-- Flight Booking Added -->
        <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
        <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
        <a href="/community"><i class="fas fa-users"></i> Community</a>
        <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>
</nav>

<!-- Hero with updated buttons -->
<section class="hero">
    <div class="hero-content">
        <h1>Plan Your Perfect Journey with AI</h1>
        <p>Personalized travel recommendations based on your mood, preferences, and budget. Discover destinations you'll love.</p>
        <div class="hero-buttons">
            <button class="primary-button" onclick="window.location.href='/plan-trip'">
                <i class="fas fa-magic"></i> Start Planning Now
            </button>
            <button class="secondary-button" onclick="window.location.href='/flights'">
                <i class="fas fa-plane"></i> Book Flights
            </button>
        </div>
    </div>
</section>

<!-- Quick Plan Form -->
<div class="quick-plan">
    <h2 class="section-title">Quick Trip Builder</h2>
    <p class="section-subtitle">Get instant recommendations by filling these simple preferences</p>
    <div class="quick-plan-form">
        <div class="form-group">
            <label>Current Mood</label>
            <select id="moodSelect">
                <option value="adventurous">Adventurous</option>
                <option value="relaxed">Relaxed</option>
                <option value="cultural">Cultural</option>
                <option value="romantic">Romantic</option>
                <option value="foodie">Foodie</option>
            </select>
        </div>
        <div class="form-group">
            <label>Budget Range</label>
            <select id="budgetSelect">
                <option value="budget">Budget Friendly</option>
                <option value="mid">Mid Range</option>
                <option value="luxury">Luxury</option>
            </select>
        </div>
        <div class="form-group">
            <label>Travel Duration</label>
            <select id="durationSelect">
                <option value="weekend">Weekend Getaway</option>
                <option value="week">One Week</option>
                <option value="long">Extended Trip</option>
            </select>
        </div>
        <div class="form-group">
            <label>Companion</label>
            <select id="companionSelect">
                <option value="solo">Solo Travel</option>
                <option value="couple">Couple</option>
                <option value="family">Family</option>
                <option value="friends">Friends</option>
            </select>
        </div>
    </div>
    <div style="text-align: center; margin-top: 30px;">
        <button class="primary-button" onclick="generateQuickPlan()">
            <i class="fas fa-robot"></i> Generate AI Suggestions
        </button>
    </div>
</div>

<!-- Feature Tiles with Flight Booking Tile -->
<div class="tile-grid">
    <div class="tile">
        <h3><i class="fas fa-plane"></i> Easy Flight Booking</h3> <!-- Updated tile -->
        <p>Search and book flights worldwide with our integrated flight booking system. Get the best deals on airfare.</p>
    </div>
    <div class="tile">
        <h3><i class="fas fa-brain"></i> AI Mood-Based Suggestions</h3>
        <p>Tell us how you feel—adventurous, relaxed, cultural—and get personalized destination recommendations.</p>
    </div>
    <div class="tile">
        <h3><i class="fas fa-sliders-h"></i> Smart Budget Optimization</h3>
        <p>Set your budget and let our algorithm find the best flights, accommodations, and activities within your range.</p>
    </div>
</div>

<!-- Slideshow Section -->
<section class="slideshow-section">
    <h2 class="section-title">Featured Destinations</h2>
    <p class="section-subtitle">Discover handpicked destinations curated by our travel experts</p>

    <div class="slideshow-container">
        <div class="slides">
            <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1516496636080-14fb876e029d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Amalfi Coast, Italy</h3>
                    <p>Experience the breathtaking beauty of Italy's coastline with its colorful cliffside villages, delicious cuisine, and Mediterranean charm.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963?ixlib=rb-4.0.3&auto=format&fit=crop&w=2067&q=80');">
                <div class="slide-content">
                    <h3>Bali, Indonesia</h3>
                    <p>Find your inner peace in Bali's spiritual retreats, lush rice terraces, and pristine beaches. Perfect for relaxation and adventure.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Santorini, Greece</h3>
                    <p>Marvel at the iconic white-washed buildings, stunning sunsets, and crystal-clear waters of this romantic Greek island paradise.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2064&q=80');">
                <div class="slide-content">
                    <h3>Kyoto, Japan</h3>
                    <p>Step back in time with ancient temples, traditional tea houses, and the magical beauty of cherry blossom season in Kyoto.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1579530190412-b35a65e17c8d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2066&q=80');">
                <div class="slide-content">
                    <h3>Swiss Alps</h3>
                    <p>Embrace adventure in the majestic Swiss Alps with breathtaking mountain views, skiing, and luxury mountain resorts.</p>
                </div>
            </div>
            <!-- Additional slides -->
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1513326738677-b964603b136d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Marrakech, Morocco</h3>
                    <p>Discover vibrant souks, stunning palaces, and rich cultural heritage in this enchanting North African city.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Maldives Overwater Bungalows</h3>
                    <p>Experience ultimate luxury in crystal-clear turquoise waters with private villas and world-class diving.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1543832923-44667a44c804?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>New York City, USA</h3>
                    <p>Explore the city that never sleeps with iconic landmarks, Broadway shows, and diverse culinary experiences.</p>
                </div>
            </div>
        </div>

        <div class="slide-number">1 / 8</div>

        <div class="slide-controls">
            <button class="slide-btn prev-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slide-btn next-btn">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="slide-indicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
            <span class="indicator" data-slide="3"></span>
            <span class="indicator" data-slide="4"></span>
            <span class="indicator" data-slide="5"></span>
            <span class="indicator" data-slide="6"></span>
            <span class="indicator" data-slide="7"></span>
        </div>
    </div>
</section>

<!-- Discover Section with updated button -->
<section class="discover-section">
    <div class="discover-header">
        <h2 class="section-title">Discover Trending Destinations</h2>
        <button class="secondary-button" onclick="window.location.href='/discover'">
            View All <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <div class="filter-tags">
        <span class="filter-tag active">All</span>
        <span class="filter-tag">Tropical</span>
        <span class="filter-tag">Mountain</span>
        <span class="filter-tag">Historical</span>
        <span class="filter-tag">Beach</span>
        <span class="filter-tag">Food</span>
        <span class="filter-tag">Art &amp; Culture</span>
        <span class="filter-tag">Eco-Tourism</span>
    </div>

    <div class="destinations-grid">
        <div class="destination-card">
            <div class="destination-image" style="background-image: url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963');"></div>
            <div class="destination-content">
                <h3>Bali, Indonesia</h3>
                <div class="destination-meta">
                    <span class="price-tag">Premium</span>
                    <span class="mood-indicator"><i class="fas fa-spa"></i> Relaxed</span>
                </div>
                <p>Perfect for yoga retreats and beach relaxation with stunning temples.</p>
                <button class="primary-button" onclick="window.location.href='/flights?destination=bali'">
                    <i class="fas fa-plane"></i> Book Flights
                </button>
            </div>
        </div>

        <div class="destination-card">
            <div class="destination-image" style="background-image: url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f');"></div>
            <div class="destination-content">
                <h3>Kyoto, Japan</h3>
                <div class="destination-meta">
                    <span class="price-tag">Luxury</span>
                    <span class="mood-indicator"><i class="fas fa-landmark"></i> Cultural</span>
                </div>
                <p>Ancient temples, traditional tea houses, and beautiful cherry blossoms.</p>
                <button class="primary-button" onclick="window.location.href='/flights?destination=kyoto'">
                    <i class="fas fa-plane"></i> Book Flights
                </button>
            </div>
        </div>

        <div class="destination-card">
            <div class="destination-image" style="background-image: url('https://images.unsplash.com/photo-1579530190412-b35a65e17c8d');"></div>
            <div class="destination-content">
                <h3>Swiss Alps</h3>
                <div class="destination-meta">
                    <span class="price-tag">Luxury</span>
                    <span class="mood-indicator"><i class="fas fa-mountain"></i> Adventurous</span>
                </div>
                <p>Breathtaking mountain views, skiing, and luxury mountain resorts.</p>
                <button class="primary-button" onclick="window.location.href='/flights?destination=alps'">
                    <i class="fas fa-plane"></i> Book Flights
                </button>
            </div>
        </div>

        <div class="destination-card">
            <div class="destination-image" style="background-image: url('https://images.unsplash.com/photo-1578662996442-48f60103fc96');"></div>
            <div class="destination-content">
                <h3>Santorini, Greece</h3>
                <div class="destination-meta">
                    <span class="price-tag">Premium</span>
                    <span class="mood-indicator"><i class="fas fa-heart"></i> Romantic</span>
                </div>
                <p>White-washed buildings, stunning sunsets, and crystal clear waters.</p>
                <button class="primary-button" onclick="window.location.href='/flights?destination=santorini'">
                    <i class="fas fa-plane"></i> Book Flights
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Quick Flight Booking Section -->
<div style="max-width: 1200px; margin: 60px auto; padding: 40px; background: var(--card-bg); border-radius: 6px; border: 1px solid var(--border);">
    <h2 class="section-title">Ready to Fly?</h2>
    <p class="section-subtitle">Find and book flights to your dream destinations</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px;">
        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid var(--border); cursor: pointer;" onclick="window.location.href='/flights'">
            <div style="font-size: 48px; color: var(--deep); margin-bottom: 20px;">
                <i class="fas fa-search"></i>
            </div>
            <h3 style="color: var(--deep); margin-bottom: 10px;">Search Flights</h3>
            <p style="color: var(--text-muted);">Find flights worldwide with flexible dates</p>
        </div>

        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid var(--border); cursor: pointer;" onclick="window.location.href='/flights/create'">
            <div style="font-size: 48px; color: var(--deep); margin-bottom: 20px;">
                <i class="fas fa-plus-circle"></i>
            </div>
            <h3 style="color: var(--deep); margin-bottom: 10px;">Create Flight</h3>
            <p style="color: var(--text-muted);">Add custom flight options</p>
        </div>

        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid var(--border); cursor: pointer;" onclick="window.location.href='/bookings'">
            <div style="font-size: 48px; color: var(--deep); margin-bottom: 20px;">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3 style="color: var(--deep); margin-bottom: 10px;">My Bookings</h3>
            <p style="color: var(--text-muted);">View and manage your bookings</p>
        </div>
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <button class="primary-button" onclick="window.location.href='/flights'" style="padding: 15px 40px; font-size: 16px;">
            <i class="fas fa-plane"></i> Start Booking Flights Now
        </button>
    </div>
</div>

<!-- Explore Categories -->
<div style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
    <h2 class="section-title">Explore By Travel Style</h2>
    <p class="section-subtitle">Find destinations that match your preferred travel experience</p>

    <div class="explore-categories">
        <div class="category-card">
            <div class="category-icon"><i class="fas fa-hiking"></i></div>
            <h3>Adventure Travel</h3>
            <p>Hiking, trekking, and extreme sports destinations</p>
        </div>
        <div class="category-card">
            <div class="category-icon"><i class="fas fa-umbrella-beach"></i></div>
            <h3>Beach &amp; Relaxation</h3>
            <p>Perfect spots for sunbathing and unwinding</p>
        </div>
        <div class="category-card">
            <div class="category-icon"><i class="fas fa-landmark"></i></div>
            <h3>Cultural Immersion</h3>
            <p>Historical sites and cultural experiences</p>
        </div>
        <div class="category-card">
            <div class="category-icon"><i class="fas fa-utensils"></i></div>
            <h3>Culinary Tours</h3>
            <p>Foodie paradises and cooking experiences</p>
        </div>
    </div>
</div>

<!-- How It Works -->
<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2 class="section-title">How It Works</h2>
    <div class="how-it-works-grid">
        <div class="how-step">
            <div class="how-step-number">1</div>
            <h3>Set Your Preferences</h3>
            <p>Choose your mood, travel dates, budget, and interests using our intuitive preference selector.</p>
        </div>
        <div class="how-step">
            <div class="how-step-number">2</div>
            <h3>Get AI Recommendations</h3>
            <p>Our algorithm analyzes thousands of destinations to suggest perfect matches for your trip.</p>
        </div>
        <div class="how-step">
            <div class="how-step-number">3</div>
            <h3>Build &amp; Book Itinerary</h3>
            <p>Customize your trip plan, add activities, and book flights directly through our system.</p>
        </div>
    </div>
</div>

<!-- AI Features Banner -->
<div class="ai-features-banner">
    <div style="text-align: center;">
        <h2>Powered by Advanced AI</h2>
        <p>Our intelligent algorithms analyze millions of data points to create your perfect trip</p>
    </div>
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number">10K+</div>
            <div>Destinations Analyzed</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">95%</div>
            <div>User Satisfaction Rate</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div>Real-Time Updates</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">50K+</div>
            <div>Happy Travelers</div>
        </div>
    </div>
</div>

<!-- Advanced Smart Features -->
<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2 class="section-title">Advanced Smart Features</h2>
    <p class="section-subtitle">Experience the future of travel planning with our AI-powered tools</p>

    <div class="smart-features-grid">
        <div class="smart-feature-card">
            <i class="fas fa-plane feature-icon"></i> <!-- Updated icon -->
            <h3>Smart Flight Booking</h3>
            <p>AI-powered flight search finds the best deals, optimal routes, and perfect timing for your travels.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-robot feature-icon"></i>
            <h3>Predictive Weather Planning</h3>
            <p>AI predicts optimal travel dates based on historical weather patterns and seasonal trends at your chosen destinations.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-chart-pie feature-icon"></i>
            <h3>Real-Time Cost Analysis</h3>
            <p>Live price tracking for flights, hotels, and activities with alerts for price drops and special deals.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-heartbeat feature-icon"></i>
            <h3>Mood &amp; Interest Matching</h3>
            <p>Advanced personality profiling to match destinations with your emotional state and personal interests.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-sync-alt feature-icon"></i>
            <h3>Dynamic Itinerary Adjuster</h3>
            <p>Automatically suggests itinerary changes based on real-time factors like traffic, closures, or weather changes.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-leaf feature-icon"></i>
            <h3>Sustainable Travel Options</h3>
            <p>Highlights eco-friendly accommodations, low-carbon transportation, and sustainable tourism activities.</p>
        </div>
    </div>
</div>

<!-- Testimonials -->
<section class="testimonials">
    <div class="testimonial-container">
        <h2 class="section-title">What Travelers Say</h2>
        <p class="section-subtitle">Join thousands of satisfied travelers who discovered their perfect trips</p>

        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p>"The flight booking was so easy! I found the perfect flight to Bali at a great price. The AI suggestions helped me choose the best travel dates."</p>
                <div class="user-info">
                    <div class="user-avatar">SJ</div>
                    <div>
                        <div class="user-name">Sarah Johnson</div>
                        <div class="user-trip">Traveled to Japan, March 2024</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p>"As a solo traveler, the flight booking system found me direct flights with great layovers. The price alerts saved me 30% on my ticket!"</p>
                <div class="user-info">
                    <div class="user-avatar">MR</div>
                    <div>
                        <div class="user-name">Michael Roberts</div>
                        <div class="user-trip">Solo Traveler, Multiple Trips</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p>"Booking flights for our family vacation was seamless. The system found flights that worked for everyone's schedule and budget."</p>
                <div class="user-info">
                    <div class="user-avatar">AC</div>
                    <div>
                        <div class="user-name">Anna Chen</div>
                        <div class="user-trip">Family Trip to Bali, 2024</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<div class="newsletter">
    <h2 class="section-title">Get Travel Inspiration</h2>
    <p class="section-subtitle">Subscribe to receive weekly destination ideas, travel tips, and exclusive deals</p>
    <div class="newsletter-input">
        <input type="email" placeholder="Enter your email address">
        <button onclick="subscribeNewsletter()">
            <i class="fas fa-paper-plane"></i> Subscribe
        </button>
    </div>
    <p class="privacy">We respect your privacy. Unsubscribe at any time.</p>
</div>

<!-- Footer -->
<footer class="footer">
    <div style="max-width: 1200px; margin: 0 auto;">
        <p>© 2026 Smart Trip Planner | Laravel Web Application Project | Created By Lenard Tivanani Hlabangwana</p>
        <div style="margin-top: 15px;">
            <a href="#"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-laravel"></i></a>
            <a href="#"><i class="fas fa-graduation-cap"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<script>
    // Enhanced Slideshow functionality with Diffusing Effect
    let currentSlide = 0;
    let nextSlideIndex = 0;
    let isTransitioning = false;
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const slideNumber = document.querySelector('.slide-number');
    const totalSlides = slides.length;
    let slideInterval;
    let slideshowDirection = 1; // 1 for forward, -1 for backward

    function updateSlide(immediate = false) {
        if (isTransitioning) return;
        isTransitioning = true;

        // Remove active and exiting classes from all slides
        slides.forEach(slide => {
            slide.classList.remove('active', 'exiting');
        });

        // Add exiting animation to current slide if not immediate
        if (!immediate && slides[currentSlide]) {
            slides[currentSlide].classList.add('exiting');
        }

        indicators.forEach(indicator => indicator.classList.remove('active'));

        // After a short delay, show the next slide with diffusing effect
        setTimeout(() => {
            // Remove exiting class from previous slide
            if (slides[currentSlide]) {
                slides[currentSlide].classList.remove('exiting');
            }

            // Add active class to new slide
            slides[nextSlideIndex].classList.add('active');
            indicators[nextSlideIndex].classList.add('active');
            slideNumber.textContent = `${nextSlideIndex + 1} / ${totalSlides}`;

            currentSlide = nextSlideIndex;

            // Allow next transition after animation completes
            setTimeout(() => {
                isTransitioning = false;
            }, 1200);
        }, immediate ? 0 : 300);
    }

    function nextSlide() {
        if (isTransitioning) return;
        slideshowDirection = 1;
        nextSlideIndex = (currentSlide + 1) % totalSlides;
        updateSlide();
    }

    function prevSlide() {
        if (isTransitioning) return;
        slideshowDirection = -1;
        nextSlideIndex = (currentSlide - 1 + totalSlides) % totalSlides;
        updateSlide();
    }

    function goToSlide(index) {
        if (isTransitioning || index === currentSlide) return;
        slideshowDirection = index > currentSlide ? 1 : -1;
        nextSlideIndex = index;
        updateSlide();
    }

    function startAutoSlide() {
        clearInterval(slideInterval);
        slideInterval = setInterval(() => {
            if (slideshowDirection === 1) {
                nextSlide();
            } else {
                prevSlide();
            }

            // Occasionally reverse direction for variety
            if (Math.random() < 0.1) { // 10% chance to reverse
                slideshowDirection *= -1;
            }
        }, 6000);
    }

    function stopAutoSlide() {
        clearInterval(slideInterval);
    }

    // Event listeners for slideshow
    document.querySelector('.next-btn').addEventListener('click', () => {
        slideshowDirection = 1;
        nextSlide();
        startAutoSlide();
    });

    document.querySelector('.prev-btn').addEventListener('click', () => {
        slideshowDirection = -1;
        prevSlide();
        startAutoSlide();
    });

    indicators.forEach(indicator => {
        indicator.addEventListener('click', function() {
            const slideIndex = parseInt(this.getAttribute('data-slide'));
            goToSlide(slideIndex);
            startAutoSlide();
        });
    });

    // Pause auto-slide on hover
    const slideshowContainer = document.querySelector('.slideshow-container');
    slideshowContainer.addEventListener('mouseenter', stopAutoSlide);
    slideshowContainer.addEventListener('mouseleave', startAutoSlide);

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            startAutoSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            startAutoSlide();
        }
    });

    // Initialize slideshow
    updateSlide(true);
    startAutoSlide();

    // Quick Plan Form Functionality
    function generateQuickPlan() {
        const mood = document.getElementById('moodSelect').value;
        const budget = document.getElementById('budgetSelect').value;
        const duration = document.getElementById('durationSelect').value;
        const companion = document.getElementById('companionSelect').value;

        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
        button.disabled = true;

        // Show AI suggestions based on selections
        setTimeout(() => {
            // Create modal with AI suggestions
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;

            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: var(--card-bg);
                padding: 40px;
                border-radius: 10px;
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                position: relative;
                border: 2px solid var(--gold);
            `;

            // Get suggestion based on selections
            const suggestions = getAISuggestions(mood, budget, duration, companion);

            modalContent.innerHTML = `
                <h2 style="color: var(--deep); margin-top: 0;">AI Travel Suggestions</h2>
                <p>Based on your preferences:</p>
                <ul style="text-align: left; color: var(--text-muted);">
                    <li><strong>Mood:</strong> ${getMoodText(mood)}</li>
                    <li><strong>Budget:</strong> ${getBudgetText(budget)}</li>
                    <li><strong>Duration:</strong> ${getDurationText(duration)}</li>
                    <li><strong>Companion:</strong> ${getCompanionText(companion)}</li>
                </ul>
                <div style="margin: 20px 0; padding: 20px; background: rgba(201,169,110,0.1); border-radius: 8px;">
                    <h3 style="color: var(--deep);">Recommended Destination</h3>
                    <p><strong>${suggestions.destination}</strong></p>
                    <p>${suggestions.description}</p>
                    <p><strong>Estimated Cost:</strong> ${suggestions.cost}</p>
                    <p><strong>Best Time to Visit:</strong> ${suggestions.bestTime}</p>
                    <p><strong>Key Activities:</strong> ${suggestions.activities}</p>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button class="primary-button" onclick="this.closest('div[style*=\"position: fixed\"]').remove()" style="flex: 1;">
                        Close
                    </button>
                    <button class="primary-button" onclick="window.location.href='/flights?destination=${suggestions.destination.toLowerCase().split(',')[0]}&mood=${mood}&budget=${budget}'" style="flex: 1; background: var(--deep); color: var(--text-light);">
                        <i class="fas fa-plane"></i> Book Flights
                    </button>
                </div>
            `;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            // Close modal on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });

            button.innerHTML = originalText;
            button.disabled = false;
        }, 1500);
    }

    // AI Suggestions logic
    function getAISuggestions(mood, budget, duration, companion) {
        const suggestions = {
            adventurous: {
                budget: {
                    solo: {
                        destination: "Nepal - Everest Base Camp",
                        description: "Perfect for solo trekkers on a budget. Experience the Himalayas and meet fellow adventurers.",
                        cost: "$800-$1200",
                        bestTime: "March-May, Sept-Nov",
                        activities: "Trekking, mountain views, local culture"
                    },
                    friends: {
                        destination: "Costa Rica - Arenal Volcano",
                        description: "Group adventure with zip-lining, hiking, and hot springs. Perfect for friends.",
                        cost: "$1000-$1500 per person",
                        bestTime: "December-April",
                        activities: "Volcano hiking, zip-lining, hot springs"
                    }
                },
                luxury: {
                    couple: {
                        destination: "Swiss Alps - Jungfrau Region",
                        description: "Luxury mountain experience with private guides and alpine resorts.",
                        cost: "$5000-$8000",
                        bestTime: "June-September",
                        activities: "Skiing, mountain climbing, luxury resorts"
                    }
                }
            },
            relaxed: {
                budget: {
                    solo: {
                        destination: "Thailand - Koh Lanta",
                        description: "Peaceful island with affordable bungalows and quiet beaches.",
                        cost: "$600-$900",
                        bestTime: "November-April",
                        activities: "Beach relaxation, yoga, snorkeling"
                    }
                },
                luxury: {
                    couple: {
                        destination: "Maldives - Private Island Resort",
                        description: "Ultimate relaxation with overwater villas and private beaches.",
                        cost: "$8000-$15000",
                        bestTime: "November-April",
                        activities: "Spa treatments, private dining, snorkeling"
                    }
                }
            },
            cultural: {
                mid: {
                    family: {
                        destination: "Italy - Rome & Florence",
                        description: "Perfect cultural journey for families with historical sites and amazing food.",
                        cost: "$4000-$6000",
                        bestTime: "April-June, September-October",
                        activities: "Museums, historical sites, cooking classes"
                    }
                }
            }
        };

        // Default suggestion
        let suggestion = {
            destination: "Bali, Indonesia",
            description: "A perfect blend of culture, relaxation, and adventure for all types of travelers.",
            cost: "$1500-$3000",
            bestTime: "April-October",
            activities: "Temples, beaches, cultural shows, hiking"
        };

        // Try to find specific suggestion
        if (suggestions[mood] && suggestions[mood][budget] && suggestions[mood][budget][companion]) {
            suggestion = suggestions[mood][budget][companion];
        } else if (suggestions[mood] && suggestions[mood][budget]) {
            // Fallback to first available companion type
            const availableCompanions = Object.keys(suggestions[mood][budget]);
            if (availableCompanions.length > 0) {
                suggestion = suggestions[mood][budget][availableCompanions[0]];
            }
        }

        return suggestion;
    }

    function getMoodText(mood) {
        const moods = {
            adventurous: "Adventurous 🏔️",
            relaxed: "Relaxed 🌴",
            cultural: "Cultural 🏛️",
            romantic: "Romantic 💖",
            foodie: "Foodie 🍽️"
        };
        return moods[mood] || mood;
    }

    function getBudgetText(budget) {
        const budgets = {
            budget: "Budget Friendly 💰",
            mid: "Mid Range 💵",
            luxury: "Luxury 💎"
        };
        return budgets[budget] || budget;
    }

    function getDurationText(duration) {
        const durations = {
            weekend: "Weekend Getaway 🚗",
            week: "One Week ✈️",
            long: "Extended Trip 🌎"
        };
        return durations[duration] || duration;
    }

    function getCompanionText(companion) {
        const companions = {
            solo: "Solo Travel 🧍",
            couple: "Couple 👫",
            family: "Family 👨‍👩‍👧‍👦",
            friends: "Friends 👯"
        };
        return companions[companion] || companion;
    }

    // Newsletter Subscription
    function subscribeNewsletter() {
        const emailInput = document.querySelector('.newsletter-input input');
        const email = emailInput.value;

        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address');
            return;
        }

        const button = document.querySelector('.newsletter-input button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
        button.style.background = '#6b8f6b';
        button.disabled = true;

        // In a real app, you would send this to a server
        console.log('Newsletter subscription:', email);

        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = '';
            button.disabled = false;
            emailInput.value = '';

            // Show success message
            const successMsg = document.createElement('div');
            successMsg.textContent = 'Thank you for subscribing! Check your email for confirmation.';
            successMsg.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: var(--deep);
                color: var(--text-light);
                padding: 15px 25px;
                border-radius: 5px;
                z-index: 1000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            `;
            document.body.appendChild(successMsg);
            setTimeout(() => successMsg.remove(), 3000);
        }, 3000);
    }

    // Filter tags functionality
    document.querySelectorAll('.filter-tag').forEach(tag => {
        tag.addEventListener('click', function() {
            document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.textContent.toLowerCase();
            const destinationCards = document.querySelectorAll('.destination-card');

            // Filter animation
            destinationCards.forEach(card => {
                card.style.opacity = '0.5';
                card.style.transform = 'scale(0.95)';
            });

            setTimeout(() => {
                destinationCards.forEach(card => {
                    const matches = filter === 'all' ||
                        card.querySelector('h3').textContent.toLowerCase().includes(filter) ||
                        card.querySelector('.mood-indicator').textContent.toLowerCase().includes(filter) ||
                        card.querySelector('.price-tag').textContent.toLowerCase().includes(filter);

                    if (matches) {
                        card.style.display = 'flex';
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }, 300);
        });
    });

    // Destination card hover effects
    document.querySelectorAll('.destination-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 8px 22px rgba(59,31,43,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
        });

        // Make "Book Flights" button clickable
        const bookButton = card.querySelector('.primary-button');
        if (bookButton) {
            bookButton.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent card click event
                const destination = card.querySelector('h3').textContent;
                const destinationSlug = destination.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                window.location.href = `/flights?destination=${destinationSlug}`;
            });
        }
    });

    // Category card hover effects
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 22px rgba(59,31,43,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
        });
    });

    // Tile hover effects
    document.querySelectorAll('.tile').forEach(tile => {
        tile.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 6px 18px rgba(59,31,43,0.13)';
        });
        tile.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
        });
    });

    // Stats animation on scroll
    const observerOptions = {
        threshold: 0.5
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumbers = document.querySelectorAll('.stat-number');
                statNumbers.forEach((stat, index) => {
                    setTimeout(() => {
                        stat.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            stat.style.transform = 'scale(1)';
                        }, 300);
                    }, index * 200);
                });
            }
        });
    }, observerOptions);

    const aiBanner = document.querySelector('.ai-features-banner');
    if (aiBanner) {
        observer.observe(aiBanner);
    }

    // Add click effects to buttons
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // Make testimonial cards interactive
    document.querySelectorAll('.testimonial-card').forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(1.02)';
            this.style.boxShadow = '0 8px 25px rgba(59,31,43,0.15)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
            }, 200);
        });
    });

    // Initialize the page with current year
    document.addEventListener('DOMContentLoaded', function() {
        const yearSpan = document.querySelector('.footer p');
        if (yearSpan) {
            yearSpan.innerHTML = yearSpan.innerHTML.replace('2026', new Date().getFullYear());
        }
    });
</script>

</body>
</html>o during cherry blossom season. Best trip ever!"</p>
                <div class="user-info">
                    <div class="user-avatar">SJ</div>
                    <div>
                        <div class="user-name">Sarah Johnson</div>
                        <div class="user-trip">Traveled to Japan, March 2024</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p>"As a solo traveler, safety was my concern. The app recommended destinations with great solo traveler infrastructure and connected me with local guides."</p>
                <div class="user-info">
                    <div class="user-avatar">MR</div>
                    <div>
                        <div class="user-name">Michael Roberts</div>
                        <div class="user-trip">Solo Traveler, Multiple Trips</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p>"Planning a family vacation with different ages was challenging. The AI created an itinerary that kept everyone happy. The kids loved it!"</p>
                <div class="user-info">
                    <div class="user-avatar">AC</div>
                    <div>
                        <div class="user-name">Anna Chen</div>
                        <div class="user-trip">Family Trip to Bali, 2024</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<div class="newsletter">
    <h2 class="section-title">Get Travel Inspiration</h2>
    <p class="section-subtitle">Subscribe to receive weekly destination ideas, travel tips, and exclusive deals</p>
    <div class="newsletter-input">
        <input type="email" placeholder="Enter your email address">
        <button onclick="subscribeNewsletter()">
            <i class="fas fa-paper-plane"></i> Subscribe
        </button>
    </div>
    <p class="privacy">We respect your privacy. Unsubscribe at any time.</p>
</div>

<!-- Footer -->
<footer class="footer">
    <div style="max-width: 1200px; margin: 0 auto;">
        <p>© 2026 Smart Trip Planner | Laravel Web Application Project | Created By Lenard Tivanani Hlabangwana</p>
        <div style="margin-top: 15px;">
            <a href="#"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-laravel"></i></a>
            <a href="#"><i class="fas fa-graduation-cap"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<script>
    // Enhanced Slideshow functionality with Diffusing Effect
    let currentSlide = 0;
    let nextSlideIndex = 0;
    let isTransitioning = false;
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const slideNumber = document.querySelector('.slide-number');
    const totalSlides = slides.length;
    let slideInterval;
    let slideshowDirection = 1; // 1 for forward, -1 for backward

    function updateSlide(immediate = false) {
        if (isTransitioning) return;
        isTransitioning = true;

        // Remove active and exiting classes from all slides
        slides.forEach(slide => {
            slide.classList.remove('active', 'exiting');
        });

        // Add exiting animation to current slide if not immediate
        if (!immediate && slides[currentSlide]) {
            slides[currentSlide].classList.add('exiting');
        }

        indicators.forEach(indicator => indicator.classList.remove('active'));

        // After a short delay, show the next slide with diffusing effect
        setTimeout(() => {
            // Remove exiting class from previous slide
            if (slides[currentSlide]) {
                slides[currentSlide].classList.remove('exiting');
            }

            // Add active class to new slide
            slides[nextSlideIndex].classList.add('active');
            indicators[nextSlideIndex].classList.add('active');
            slideNumber.textContent = `${nextSlideIndex + 1} / ${totalSlides}`;

            currentSlide = nextSlideIndex;

            // Allow next transition after animation completes
            setTimeout(() => {
                isTransitioning = false;
            }, 1200);
        }, immediate ? 0 : 300);
    }

    function nextSlide() {
        if (isTransitioning) return;
        slideshowDirection = 1;
        nextSlideIndex = (currentSlide + 1) % totalSlides;
        updateSlide();
    }

    function prevSlide() {
        if (isTransitioning) return;
        slideshowDirection = -1;
        nextSlideIndex = (currentSlide - 1 + totalSlides) % totalSlides;
        updateSlide();
    }

    function goToSlide(index) {
        if (isTransitioning || index === currentSlide) return;
        slideshowDirection = index > currentSlide ? 1 : -1;
        nextSlideIndex = index;
        updateSlide();
    }

    function startAutoSlide() {
        clearInterval(slideInterval);
        slideInterval = setInterval(() => {
            if (slideshowDirection === 1) {
                nextSlide();
            } else {
                prevSlide();
            }

            // Occasionally reverse direction for variety
            if (Math.random() < 0.1) { // 10% chance to reverse
                slideshowDirection *= -1;
            }
        }, 6000);
    }

    function stopAutoSlide() {
        clearInterval(slideInterval);
    }

    // Event listeners for slideshow
    document.querySelector('.next-btn').addEventListener('click', () => {
        slideshowDirection = 1;
        nextSlide();
        startAutoSlide();
    });

    document.querySelector('.prev-btn').addEventListener('click', () => {
        slideshowDirection = -1;
        prevSlide();
        startAutoSlide();
    });

    indicators.forEach(indicator => {
        indicator.addEventListener('click', function() {
            const slideIndex = parseInt(this.getAttribute('data-slide'));
            goToSlide(slideIndex);
            startAutoSlide();
        });
    });

    // Pause auto-slide on hover
    const slideshowContainer = document.querySelector('.slideshow-container');
    slideshowContainer.addEventListener('mouseenter', stopAutoSlide);
    slideshowContainer.addEventListener('mouseleave', startAutoSlide);

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            startAutoSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            startAutoSlide();
        }
    });

    // Initialize slideshow
    updateSlide(true);
    startAutoSlide();

    // Quick Plan Form Functionality
    function generateQuickPlan() {
        const mood = document.getElementById('moodSelect').value;
        const budget = document.getElementById('budgetSelect').value;
        const duration = document.getElementById('durationSelect').value;
        const companion = document.getElementById('companionSelect').value;

        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
        button.disabled = true;

        // Show AI suggestions based on selections
        setTimeout(() => {
            // Create modal with AI suggestions
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;

            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: var(--card-bg);
                padding: 40px;
                border-radius: 10px;
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                position: relative;
                border: 2px solid var(--gold);
            `;

            // Get suggestion based on selections
            const suggestions = getAISuggestions(mood, budget, duration, companion);

            modalContent.innerHTML = `
                <h2 style="color: var(--deep); margin-top: 0;">AI Travel Suggestions</h2>
                <p>Based on your preferences:</p>
                <ul style="text-align: left; color: var(--text-muted);">
                    <li><strong>Mood:</strong> ${getMoodText(mood)}</li>
                    <li><strong>Budget:</strong> ${getBudgetText(budget)}</li>
                    <li><strong>Duration:</strong> ${getDurationText(duration)}</li>
                    <li><strong>Companion:</strong> ${getCompanionText(companion)}</li>
                </ul>
                <div style="margin: 20px 0; padding: 20px; background: rgba(201,169,110,0.1); border-radius: 8px;">
                    <h3 style="color: var(--deep);">Recommended Destination</h3>
                    <p><strong>${suggestions.destination}</strong></p>
                    <p>${suggestions.description}</p>
                    <p><strong>Estimated Cost:</strong> ${suggestions.cost}</p>
                    <p><strong>Best Time to Visit:</strong> ${suggestions.bestTime}</p>
                    <p><strong>Key Activities:</strong> ${suggestions.activities}</p>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button class="primary-button" onclick="this.closest('div[style*=\"position: fixed\"]').remove()" style="flex: 1;">
                        Close
                    </button>
                    <button class="primary-button" onclick="window.location.href='/plan-trip?mood=${mood}&budget=${budget}&duration=${duration}&companion=${companion}'" style="flex: 1; background: var(--deep); color: var(--text-light);">
                        <i class="fas fa-arrow-right"></i> Start Planning
                    </button>
                </div>
            `;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            // Close modal on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });

            button.innerHTML = originalText;
            button.disabled = false;
        }, 1500);
    }

    // AI Suggestions logic
    function getAISuggestions(mood, budget, duration, companion) {
        const suggestions = {
            adventurous: {
                budget: {
                    solo: {
                        destination: "Nepal - Everest Base Camp",
                        description: "Perfect for solo trekkers on a budget. Experience the Himalayas and meet fellow adventurers.",
                        cost: "$800-$1200",
                        bestTime: "March-May, Sept-Nov",
                        activities: "Trekking, mountain views, local culture"
                    },
                    friends: {
                        destination: "Costa Rica - Arenal Volcano",
                        description: "Group adventure with zip-lining, hiking, and hot springs. Perfect for friends.",
                        cost: "$1000-$1500 per person",
                        bestTime: "December-April",
                        activities: "Volcano hiking, zip-lining, hot springs"
                    }
                },
                luxury: {
                    couple: {
                        destination: "Swiss Alps - Jungfrau Region",
                        description: "Luxury mountain experience with private guides and alpine resorts.",
                        cost: "$5000-$8000",
                        bestTime: "June-September",
                        activities: "Skiing, mountain climbing, luxury resorts"
                    }
                }
            },
            relaxed: {
                budget: {
                    solo: {
                        destination: "Thailand - Koh Lanta",
                        description: "Peaceful island with affordable bungalows and quiet beaches.",
                        cost: "$600-$900",
                        bestTime: "November-April",
                        activities: "Beach relaxation, yoga, snorkeling"
                    }
                },
                luxury: {
                    couple: {
                        destination: "Maldives - Private Island Resort",
                        description: "Ultimate relaxation with overwater villas and private beaches.",
                        cost: "$8000-$15000",
                        bestTime: "November-April",
                        activities: "Spa treatments, private dining, snorkeling"
                    }
                }
            },
            cultural: {
                mid: {
                    family: {
                        destination: "Italy - Rome & Florence",
                        description: "Perfect cultural journey for families with historical sites and amazing food.",
                        cost: "$4000-$6000",
                        bestTime: "April-June, September-October",
                        activities: "Museums, historical sites, cooking classes"
                    }
                }
            }
        };

        // Default suggestion
        let suggestion = {
            destination: "Bali, Indonesia",
            description: "A perfect blend of culture, relaxation, and adventure for all types of travelers.",
            cost: "$1500-$3000",
            bestTime: "April-October",
            activities: "Temples, beaches, cultural shows, hiking"
        };

        // Try to find specific suggestion
        if (suggestions[mood] && suggestions[mood][budget] && suggestions[mood][budget][companion]) {
            suggestion = suggestions[mood][budget][companion];
        } else if (suggestions[mood] && suggestions[mood][budget]) {
            // Fallback to first available companion type
            const availableCompanions = Object.keys(suggestions[mood][budget]);
            if (availableCompanions.length > 0) {
                suggestion = suggestions[mood][budget][availableCompanions[0]];
            }
        }

        return suggestion;
    }

    function getMoodText(mood) {
        const moods = {
            adventurous: "Adventurous 🏔️",
            relaxed: "Relaxed 🌴",
            cultural: "Cultural 🏛️",
            romantic: "Romantic 💖",
            foodie: "Foodie 🍽️"
        };
        return moods[mood] || mood;
    }

    function getBudgetText(budget) {
        const budgets = {
            budget: "Budget Friendly 💰",
            mid: "Mid Range 💵",
            luxury: "Luxury 💎"
        };
        return budgets[budget] || budget;
    }

    function getDurationText(duration) {
        const durations = {
            weekend: "Weekend Getaway 🚗",
            week: "One Week ✈️",
            long: "Extended Trip 🌎"
        };
        return durations[duration] || duration;
    }

    function getCompanionText(companion) {
        const companions = {
            solo: "Solo Travel 🧍",
            couple: "Couple 👫",
            family: "Family 👨‍👩‍👧‍👦",
            friends: "Friends 👯"
        };
        return companions[companion] || companion;
    }

    // Newsletter Subscription
    function subscribeNewsletter() {
        const emailInput = document.querySelector('.newsletter-input input');
        const email = emailInput.value;

        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address');
            return;
        }

        const button = document.querySelector('.newsletter-input button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
        button.style.background = '#6b8f6b';
        button.disabled = true;

        // In a real app, you would send this to a server
        console.log('Newsletter subscription:', email);

        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = '';
            button.disabled = false;
            emailInput.value = '';

            // Show success message
            const successMsg = document.createElement('div');
            successMsg.textContent = 'Thank you for subscribing! Check your email for confirmation.';
            successMsg.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: var(--deep);
                color: var(--text-light);
                padding: 15px 25px;
                border-radius: 5px;
                z-index: 1000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            `;
            document.body.appendChild(successMsg);
            setTimeout(() => successMsg.remove(), 3000);
        }, 3000);
    }

    // Filter tags functionality
    document.querySelectorAll('.filter-tag').forEach(tag => {
        tag.addEventListener('click', function() {
            document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.textContent.toLowerCase();
            const destinationCards = document.querySelectorAll('.destination-card');

            // Filter animation
            destinationCards.forEach(card => {
                card.style.opacity = '0.5';
                card.style.transform = 'scale(0.95)';
            });

            setTimeout(() => {
                destinationCards.forEach(card => {
                    const matches = filter === 'all' ||
                        card.querySelector('h3').textContent.toLowerCase().includes(filter) ||
                        card.querySelector('.mood-indicator').textContent.toLowerCase().includes(filter) ||
                        card.querySelector('.price-tag').textContent.toLowerCase().includes(filter);

                    if (matches) {
                        card.style.display = 'flex';
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }, 300);
        });
    });

    // Destination card hover effects
    document.querySelectorAll('.destination-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 8px 22px rgba(59,31,43,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
        });

        // Make entire card clickable to go to destination page
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on the Explore button
            if (!e.target.closest('.primary-button')) {
                const destination = this.querySelector('h3').textContent;
                const destinationSlug = destination.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                window.location.href = `/destination/${destinationSlug}`;
            }
        });
    });

    // Category card hover effects
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 22px rgba(59,31,43,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
        });
    });

    // Tile hover effects
    document.querySelectorAll('.tile').forEach(tile => {
        tile.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 6px 18px rgba(59,31,43,0.13)';
        });
        tile.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
        });
    });

    // Stats animation on scroll
    const observerOptions = {
        threshold: 0.5
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumbers = document.querySelectorAll('.stat-number');
                statNumbers.forEach((stat, index) => {
                    setTimeout(() => {
                        stat.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            stat.style.transform = 'scale(1)';
                        }, 300);
                    }, index * 200);
                });
            }
        });
    }, observerOptions);

    const aiBanner = document.querySelector('.ai-features-banner');
    if (aiBanner) {
        observer.observe(aiBanner);
    }

    // Add click effects to buttons
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // Make testimonial cards interactive
    document.querySelectorAll('.testimonial-card').forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(1.02)';
            this.style.boxShadow = '0 8px 25px rgba(59,31,43,0.15)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
            }, 200);
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({ top: targetElement.offsetTop - 80, behavior: 'smooth' });
            }
        });
    });

    // Initialize the page with current year
    document.addEventListener('DOMContentLoaded', function() {
        const yearSpan = document.querySelector('.footer p');
        if (yearSpan) {
            yearSpan.innerHTML = yearSpan.innerHTML.replace('2026', new Date().getFullYear());
        }
    });
</script>

</body>
</html>
