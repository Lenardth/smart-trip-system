<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discover — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --deep: #3b1f2b;
            --deep-alt: #4d2a3a;
            --gold: #c9a96e;
            --gold-hover: #b8955a;
            --cream: #f5f0eb;
            --card-bg: #fff8f2;
            --border: #e2d5c7;
            --border-soft: #d4c4b0;
            --text-light: #f5e6d3;
            --text-muted: #6b5b4f;
            --text-sub: #d4c4b0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', serif;
            background: var(--cream);
            color: #2c2c2c;
            line-height: 1.6;
        }

        /* Header */
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

        /* Navigation */
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
            position: relative;
        }

        .nav-container a:hover,
        .nav-container a.active {
            background: rgba(59, 31, 43, 0.18);
            transform: translateY(-2px);
        }

        /* Wishlist Counter in Nav */
        .wishlist-counter {
            position: relative;
        }

        .wishlist-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--deep);
            color: var(--text-light);
            font-size: 11px;
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Hero Section */
        .page-hero {
            background: linear-gradient(rgba(30, 15, 20, 0.6), rgba(30, 15, 20, 0.6)),
                        url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            height: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-light);
        }

        .page-hero h1 {
            font-size: 36px;
            font-weight: normal;
            letter-spacing: 1px;
            margin: 0 0 10px;
            color: var(--text-light);
        }

        .page-hero p {
            font-size: 16px;
            color: var(--text-sub);
            margin: 0 0 20px;
        }

        /* Search Bar */
        .hero-search {
            display: flex;
            gap: 10px;
            justify-content: center;
            max-width: 680px;
            margin: 0 auto;
            flex-wrap: wrap;
            position: relative;
        }

        .hero-search input {
            flex: 1;
            min-width: 300px;
            padding: 14px 50px 14px 20px;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            font-family: 'Georgia', serif;
            color: var(--deep);
            background: var(--card-bg);
        }

        .hero-search input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.3);
        }

        .search-icon {
            position: absolute;
            right: 150px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--deep);
            opacity: 0.5;
        }

        .hero-search button {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 14px 28px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
            font-family: 'Georgia', serif;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hero-search button:hover {
            background: var(--gold-hover);
        }

        /* Search Suggestions */
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border-radius: 4px;
            margin-top: 5px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 100;
            display: none;
            max-height: 300px;
            overflow-y: auto;
        }

        .suggestion-item {
            padding: 12px 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }

        .suggestion-item:hover {
            background: var(--cream);
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item i {
            color: var(--gold);
        }

        /* Main Content */
        .discover-wrap {
            max-width: 1600px;
            margin: 40px auto;
            padding: 0 20px;
        }

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

        /* Buttons */
        .primary-button {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.3s ease, box-shadow 0.3s ease;
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
        }

        .secondary-button {
            background: transparent;
            color: var(--deep);
            border: 2px solid var(--deep);
            padding: 10px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .secondary-button:hover {
            background: var(--deep);
            color: var(--text-light);
        }

        /* Filter Section */
        .filter-section {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(59, 31, 43, 0.08);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            flex: 1;
        }

        .filter-tab {
            padding: 8px 20px;
            background: var(--card-bg);
            border: 1px solid var(--deep);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: var(--deep);
            font-size: 14px;
            font-family: 'Georgia', serif;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-tab:hover,
        .filter-tab.active {
            background: var(--deep);
            color: var(--text-light);
        }

        /* Active Filters Display */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            padding: 15px;
            background: rgba(201, 169, 110, 0.1);
            border-radius: 4px;
            border-left: 4px solid var(--gold);
        }

        .active-filter-tag {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: var(--deep);
            color: var(--text-light);
            border-radius: 4px;
            font-size: 13px;
        }

        .active-filter-tag i {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .active-filter-tag i:hover {
            transform: scale(1.2);
        }

        /* Region Filter */
        .region-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .region-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
            color: var(--deep);
            font-family: 'Georgia', serif;
            font-weight: 600;
        }

        .region-pill:hover,
        .region-pill.active {
            border-color: var(--gold);
            background: #fdf0dc;
        }

        /* Results Info */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px 20px;
            background: var(--card-bg);
            border-radius: 6px;
            border-left: 4px solid var(--gold);
        }

        .results-stats {
            font-size: 15px;
            color: var(--deep);
        }

        .results-stats strong {
            color: var(--deep);
            font-size: 18px;
        }

        /* Price Range Filter */
        .price-range {
            margin-top: 15px;
            padding: 15px;
            background: rgba(201, 169, 110, 0.05);
            border-radius: 4px;
            border: 1px solid var(--border);
        }

        .price-range label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: var(--deep);
        }

        .price-slider {
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: var(--border);
            outline: none;
            -webkit-appearance: none;
        }

        .price-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--gold);
            cursor: pointer;
        }

        .price-values {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .page-btn {
            padding: 8px 16px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            color: var(--deep);
            min-width: 40px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .page-btn:hover {
            background: var(--deep);
            color: var(--text-light);
        }

        .page-btn.active {
            background: var(--deep);
            color: var(--text-light);
        }

        /* Destinations Grid */
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .destination-card {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(59, 31, 43, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .destination-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(59, 31, 43, 0.18);
        }

        .destination-image {
            height: 200px;
            width: 100%;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .dest-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--deep);
            color: var(--text-light);
            font-size: 12px;
            font-weight: bold;
            padding: 5px 12px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .destination-content {
            padding: 25px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .destination-content h3 {
            margin: 0 0 10px;
            color: var(--deep);
            font-weight: normal;
            font-size: 20px;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }

        .destination-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .price-tag {
            background: var(--gold);
            color: var(--deep);
            padding: 6px 16px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .mood-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #f5efe8;
            border-radius: 4px;
            font-size: 13px;
            color: var(--deep);
            border: 1px solid var(--border);
        }

        .destination-content p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 20px;
            flex-grow: 1;
        }

        .destination-footer {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .destination-footer button {
            flex: 1;
            padding: 10px;
            font-size: 14px;
        }

        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--deep);
            transition: all 0.3s ease;
            z-index: 2;
        }

        .wishlist-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        .wishlist-btn.active {
            color: #ff4757;
        }

        /* Featured Section */
        .featured-section {
            background: linear-gradient(135deg, var(--deep), var(--deep-alt));
            border-radius: 8px;
            padding: 50px 40px;
            margin: 60px 0;
            color: var(--text-light);
            box-shadow: 0 8px 28px rgba(59, 31, 43, 0.25);
            border: 1px solid rgba(201, 169, 110, 0.2);
        }

        .featured-section h2 {
            color: var(--text-light);
            font-weight: normal;
            font-size: 28px;
            margin-top: 0;
            letter-spacing: 1px;
        }

        .featured-section .section-title:after {
            background: var(--gold);
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .featured-card {
            background: rgba(255, 248, 242, 0.08);
            border: 1px solid rgba(201, 169, 110, 0.2);
            border-radius: 6px;
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .featured-card:hover {
            transform: translateY(-4px);
        }

        .featured-card .feat-img {
            height: 160px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .featured-card .feat-body {
            padding: 20px;
            text-align: left;
        }

        .featured-card h4 {
            color: var(--text-light);
            font-weight: normal;
            font-size: 18px;
            margin: 0 0 8px;
        }

        .featured-card p {
            color: var(--text-sub);
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }

        .feat-tag {
            display: inline-block;
            background: var(--gold);
            color: var(--deep);
            font-size: 12px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
            color: var(--deep);
        }

        .loading i {
            font-size: 32px;
            margin-bottom: 15px;
            color: var(--gold);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            display: none;
        }

        .no-results i {
            font-size: 60px;
            margin-bottom: 20px;
            color: var(--border);
        }

        /* Quick Filters */
        .quick-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .quick-filter {
            padding: 8px 16px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            color: var(--deep);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quick-filter:hover {
            background: var(--deep);
            color: var(--text-light);
        }

        .quick-filter.active {
            background: var(--deep);
            color: var(--text-light);
        }

        /* Modal Popup */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: var(--card-bg);
            margin: 50px auto;
            max-width: 800px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: var(--deep);
            color: var(--text-light);
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-weight: normal;
            font-size: 24px;
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .modal-body {
            padding: 30px;
        }

        .destination-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .detail-image {
            height: 300px;
            background-size: cover;
            background-position: center;
            border-radius: 8px;
            position: relative;
        }

        .detail-info h3 {
            color: var(--deep);
            font-size: 22px;
            margin-bottom: 15px;
        }

        .detail-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .detail-description {
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 25px;
        }

        .detail-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--deep);
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid var(--border);
        }

        /* Footer */
        .footer {
            background: var(--deep);
            color: var(--text-sub);
            text-align: center;
            padding: 40px 20px;
            margin-top: 80px;
        }

        .footer a {
            color: var(--gold);
            margin: 0 12px;
            transition: color 0.3s ease;
            text-decoration: none;
            font-size: 20px;
        }

        .footer a:hover {
            color: var(--text-light);
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--deep);
            color: var(--text-light);
            padding: 15px 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .notification.success { border-left: 4px solid #2ecc71; }
        .notification.info { border-left: 4px solid var(--gold); }
        .notification button {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
        }

        /* Accessibility Improvements */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Keyboard focus styles */
        *:focus {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
        }

        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, var(--border) 25%, var(--cream) 50%, var(--border) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Filter presets */
        .filter-presets {
            display: flex;
            gap: 10px;
            margin: 15px 0;
            flex-wrap: wrap;
        }

        .preset-btn {
            padding: 8px 16px;
            background: var(--card-bg);
            border: 1px solid var(--gold);
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            color: var(--deep);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .preset-btn:hover {
            background: var(--gold);
            color: var(--deep);
        }

        /* Export/Print buttons */
        .export-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .export-btn {
            padding: 6px 12px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            color: var(--text-muted);
            transition: all 0.3s;
        }

        .export-btn:hover {
            border-color: var(--gold);
            color: var(--deep);
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .destinations-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }

            .destination-detail {
                grid-template-columns: 1fr;
            }

            .detail-image {
                height: 250px;
            }
        }

        @media (max-width: 768px) {
            .main-header {
                justify-content: center;
                padding: 15px 20px;
            }

            .logo {
                height: 70px;
                min-width: 70px;
            }

            .logo-text {
                font-size: 26px;
            }

            .nav-container {
                flex-direction: column;
                align-items: center;
                padding: 10px;
            }

            .nav-container a {
                font-size: 14px;
                padding: 8px 12px;
            }

            .hero-search {
                flex-direction: column;
            }

            .hero-search input,
            .hero-search button {
                width: 100%;
            }

            .search-icon {
                right: 20px;
            }

            .filter-row {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .filter-tabs {
                justify-content: center;
            }

            .region-row {
                justify-content: center;
            }

            .results-info {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .destination-footer {
                flex-direction: column;
            }

            .featured-section {
                padding: 30px 20px;
            }

            .modal-content {
                margin: 20px;
                width: calc(100% - 40px);
            }

            .detail-features {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .page-hero h1 {
                font-size: 28px;
            }

            .hero-search input {
                min-width: 100%;
            }

            .destinations-grid {
                grid-template-columns: 1fr;
            }

            .featured-grid {
                grid-template-columns: 1fr;
            }

            .modal-body {
                padding: 20px;
            }

            .modal-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
</header>

<nav>
    <div class="nav-container">
        <a href="/" aria-label="Home"><i class="fas fa-home"></i> Home</a>
        <a href="/dashboard" aria-label="Dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/plan-trip" aria-label="Plan Trip"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="/flights" aria-label="Book Flights"><i class="fas fa-plane"></i> Book Flights</a>
        <a href="/discover" class="active" aria-label="Discover"><i class="fas fa-compass"></i> Discover</a>
        <a href="/destinations" aria-label="Destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
        <a href="/community" aria-label="Community"><i class="fas fa-users"></i> Community</a>
        <a href="/wishlist" class="wishlist-counter" id="wishlistLink" aria-label="Wishlist">
            <i class="fas fa-heart"></i> Wishlist
            <span class="wishlist-count" id="wishlistCount">0</span>
        </a>
        <a href="/login" aria-label="Login"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>
</nav>

<section class="page-hero">
    <div>
        <h1><i class="fas fa-compass"></i> Discover</h1>
        <p>Explore trending destinations, hidden gems, and AI-curated picks from 3000+ destinations worldwide</p>
        <div class="hero-search">
            <input type="text" id="searchInput" placeholder="Search destinations, countries, experiences, activities…" aria-label="Search destinations">
            <i class="fas fa-search search-icon" aria-hidden="true"></i>
            <button id="searchBtn" aria-label="Search"><i class="fas fa-search"></i> Search</button>
            <div class="search-suggestions" id="searchSuggestions" role="listbox" aria-label="Search suggestions"></div>
        </div>
    </div>
</section>

<div class="discover-wrap">
    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-row">
            <div class="filter-tabs" id="filterTabs" role="tablist">
                <span class="filter-tab active" data-filter="all" role="tab" aria-selected="true">
                    <i class="fas fa-globe"></i> All Destinations
                </span>
                <span class="filter-tab" data-filter="trending" role="tab" aria-selected="false">
                    <i class="fas fa-fire"></i> Trending
                </span>
                <span class="filter-tab" data-filter="ai-picks" role="tab" aria-selected="false">
                    <i class="fas fa-robot"></i> AI Picks
                </span>
                <span class="filter-tab" data-filter="beach" role="tab" aria-selected="false">
                    <i class="fas fa-umbrella-beach"></i> Beach
                </span>
                <span class="filter-tab" data-filter="mountain" role="tab" aria-selected="false">
                    <i class="fas fa-mountain"></i> Mountain
                </span>
                <span class="filter-tab" data-filter="historical" role="tab" aria-selected="false">
                    <i class="fas fa-landmark"></i> Historical
                </span>
            </div>
            <button class="secondary-button" id="resetBtn" aria-label="Reset all filters">
                <i class="fas fa-redo"></i> Reset Filters
            </button>
        </div>

        <div class="filter-row">
            <div class="filter-tabs" role="tablist">
                <span class="filter-tab" data-filter="food-culture" role="tab" aria-selected="false">
                    <i class="fas fa-utensils"></i> Food & Culture
                </span>
                <span class="filter-tab" data-filter="eco-tourism" role="tab" aria-selected="false">
                    <i class="fas fa-leaf"></i> Eco-Tourism
                </span>
                <span class="filter-tab" data-filter="romantic" role="tab" aria-selected="false">
                    <i class="fas fa-heart"></i> Romantic
                </span>
                <span class="filter-tab" data-filter="adventure" role="tab" aria-selected="false">
                    <i class="fas fa-hiking"></i> Adventure
                </span>
                <span class="filter-tab" data-filter="luxury" role="tab" aria-selected="false">
                    <i class="fas fa-crown"></i> Luxury
                </span>
                <span class="filter-tab" data-filter="budget" role="tab" aria-selected="false">
                    <i class="fas fa-wallet"></i> Budget
                </span>
            </div>
        </div>

        <!-- Filter Presets -->
        <div class="filter-presets">
            <button class="preset-btn" onclick="applyFilterPreset('familyVacation')">
                <i class="fas fa-users"></i> Family Vacation
            </button>
            <button class="preset-btn" onclick="applyFilterPreset('romanticGetaway')">
                <i class="fas fa-heart"></i> Romantic Getaway
            </button>
            <button class="preset-btn" onclick="applyFilterPreset('adventureTrip')">
                <i class="fas fa-hiking"></i> Adventure Trip
            </button>
            <button class="preset-btn" onclick="applyFilterPreset('budgetTravel')">
                <i class="fas fa-wallet"></i> Budget Travel
            </button>
        </div>

        <!-- Region Filter -->
        <div class="region-row" id="regionPills">
            <div class="region-pill active" data-region="all" role="button" tabindex="0">
                <i class="fas fa-globe"></i> All Regions
            </div>
            <div class="region-pill" data-region="asia" role="button" tabindex="0">
                <i class="fas fa-globe-asia"></i> Asia
            </div>
            <div class="region-pill" data-region="europe" role="button" tabindex="0">
                <i class="fas fa-globe-europe"></i> Europe
            </div>
            <div class="region-pill" data-region="north-america" role="button" tabindex="0">
                <i class="fas fa-globe-americas"></i> North America
            </div>
            <div class="region-pill" data-region="south-america" role="button" tabindex="0">
                <i class="fas fa-globe-americas"></i> South America
            </div>
            <div class="region-pill" data-region="africa" role="button" tabindex="0">
                <i class="fas fa-globe-africa"></i> Africa
            </div>
            <div class="region-pill" data-region="oceania" role="button" tabindex="0">
                <i class="fas fa-globe-asia"></i> Oceania
            </div>
            <div class="region-pill" data-region="middle-east" role="button" tabindex="0">
                <i class="fas fa-mosque"></i> Middle East
            </div>
        </div>

        <!-- Price Range Filter -->
        <div class="price-range">
            <label for="priceSlider"><i class="fas fa-dollar-sign"></i> Price Range</label>
            <input type="range" min="500" max="5000" value="5000" class="price-slider" id="priceSlider"
                   aria-label="Price range filter" aria-valuemin="500" aria-valuemax="5000" aria-valuenow="5000">
            <div class="price-values">
                <span>$500</span>
                <span id="priceValue">$5000+</span>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="quick-filters">
            <div class="quick-filter" data-keyword="skiing" role="button" tabindex="0">
                <i class="fas fa-skiing"></i> Skiing
            </div>
            <div class="quick-filter" data-keyword="hiking" role="button" tabindex="0">
                <i class="fas fa-hiking"></i> Hiking
            </div>
            <div class="quick-filter" data-keyword="diving" role="button" tabindex="0">
                <i class="fas fa-swimmer"></i> Diving
            </div>
            <div class="quick-filter" data-keyword="shopping" role="button" tabindex="0">
                <i class="fas fa-shopping-bag"></i> Shopping
            </div>
            <div class="quick-filter" data-keyword="nightlife" role="button" tabindex="0">
                <i class="fas fa-glass-cheers"></i> Nightlife
            </div>
            <div class="quick-filter" data-keyword="family" role="button" tabindex="0">
                <i class="fas fa-users"></i> Family
            </div>
            <div class="quick-filter" data-keyword="solo" role="button" tabindex="0">
                <i class="fas fa-user"></i> Solo
            </div>
            <div class="quick-filter" data-keyword="spa" role="button" tabindex="0">
                <i class="fas fa-spa"></i> Spa
            </div>
        </div>

        <!-- Active Filters Display -->
        <div class="active-filters" id="activeFilters"></div>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <div class="results-stats">
            Showing <strong id="resultsCount">12</strong> of <strong id="totalCount">3000+</strong> destinations
            <span id="currentFilter" style="color: var(--gold); margin-left: 15px;"></span>
        </div>
        <div>
            <select id="sortSelect" class="secondary-button" style="padding: 8px 15px; font-size: 14px;" aria-label="Sort destinations">
                <option value="popular"><i class="fas fa-fire"></i> Sort by: Popular</option>
                <option value="price-low"><i class="fas fa-sort-amount-down"></i> Price: Low to High</option>
                <option value="price-high"><i class="fas fa-sort-amount-up"></i> Price: High to Low</option>
                <option value="name"><i class="fas fa-sort-alpha-down"></i> Name: A to Z</option>
                <option value="rating"><i class="fas fa-star"></i> Rating: High to Low</option>
                <option value="reviews"><i class="fas fa-comments"></i> Most Reviews</option>
            </select>
        </div>
    </div>

    <!-- Loading -->
    <div class="loading" id="loading">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Discovering amazing destinations...</p>
    </div>

    <!-- No Results -->
    <div class="no-results" id="noResults">
        <i class="fas fa-search"></i>
        <h3>No destinations found</h3>
        <p>Try adjusting your search or filters to find more destinations</p>
        <button class="primary-button" onclick="resetFilters()" style="margin-top: 20px;">
            <i class="fas fa-redo"></i> Reset All Filters
        </button>
    </div>

    <!-- Destinations Grid -->
    <div class="destinations-grid" id="destinationsGrid">
        <!-- Dynamic content will be loaded here -->
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Dynamic pagination will be loaded here -->
    </div>

    <!-- Hidden Gems Section -->
    <div class="featured-section">
        <h2 class="section-title" style="color:var(--text-light);">
            <i class="fas fa-gem"></i> Hidden Gems
        </h2>
        <p style="color:var(--text-sub);font-size:16px;margin-top:0;">
            Destinations our AI found that most travelers overlook — but love once they visit.
        </p>
        <div class="featured-grid" id="featuredGrid">
            <!-- Featured destinations will be loaded here -->
        </div>
    </div>
</div>

<!-- Modal Popup -->
<div class="modal" id="destinationModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalBody">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Destination Details</h2>
            <button class="close-modal" id="closeModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Dynamic content will be loaded here -->
        </div>
    </div>
</div>

<footer class="footer">
    <div style="max-width:1200px;margin:0 auto;">
        <p>© 2026 Smart Trip Planner | Laravel Web Application Project</p>
        <div style="margin-top:15px;">
            <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
            <a href="#" aria-label="Laravel"><i class="fab fa-laravel"></i></a>
            <a href="#" aria-label="Education"><i class="fas fa-graduation-cap"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<!-- Hidden template for printing -->
<div id="print-template" style="display: none;"></div>

<script>
// Configuration
const ITEMS_PER_PAGE = 12;
let currentPage = 1;
let currentSort = 'popular';
let filteredDestinations = [];
let searchTimeout;

// Get CSRF token for Laravel integration
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// Large dataset with 3000+ destinations
const destinationsData = generateDestinations(3000);
const featuredData = generateFeaturedDestinations(50);

// Keyword database for advanced search
const keywordDatabase = generateKeywordDatabase();

// Active filters tracking
let activeFilters = {
    category: 'all',
    region: 'all',
    price: 5000,
    keywords: [],
    search: ''
};

// Wishlist functionality - load from localStorage
let wishlist = JSON.parse(localStorage.getItem('smartBookingWishlist')) || [];
let wishlistCount = wishlist.length;

// Search suggestions
let searchSuggestions = [];

// Filter presets
const filterPresets = {
    'familyVacation': {
        category: 'all',
        keywords: ['family', 'kids', 'safe'],
        price: 3000,
        region: 'all'
    },
    'romanticGetaway': {
        category: 'romantic',
        keywords: ['romantic', 'luxury', 'beach'],
        price: 4000,
        region: 'europe'
    },
    'adventureTrip': {
        category: 'adventure',
        keywords: ['hiking', 'mountain', 'outdoor'],
        price: 2500,
        region: 'all'
    },
    'budgetTravel': {
        category: 'budget',
        keywords: ['budget', 'affordable', 'cheap'],
        price: 1500,
        region: 'all'
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initPage();
    updateWishlistCount();
    generateSearchSuggestions();
    setupWishlistNavigation();
    initKeyboardNavigation();
    initImageLazyLoading();
});

function initPage() {
    loadDestinations();
    loadFeaturedDestinations();
    setupEventListeners();
    updateTotalCount();
    updatePriceSlider();
    updateActiveFiltersDisplay();
}

// Destination generation functions
function generateDestinations(count) {
    const destinations = [];
    const countries = [
        "Afghanistan", "Albania", "Algeria", "Andorra", "Angola",
        "Antigua and Barbuda", "Argentina", "Armenia", "Australia",
        "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh",
        "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan",
        "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil",
        "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde",
        "Cambodia", "Cameroon", "Canada", "Central African Republic",
        "Chad", "Chile", "China", "Colombia", "Comoros",
        "Congo, Democratic Republic of the", "Congo, Republic of the",
        "Costa Rica", "Côte d'Ivoire", "Croatia", "Cuba", "Cyprus",
        "Czech Republic", "Denmark", "Djibouti", "Dominica",
        "Dominican Republic", "East Timor", "Ecuador", "Egypt",
        "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia",
        "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon",
        "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada",
        "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti",
        "Honduras", "Hungary", "Iceland", "India", "Indonesia",
        "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica",
        "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati",
        "Korea, North", "Korea, South", "Kosovo", "Kuwait",
        "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho",
        "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg",
        "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali",
        "Malta", "Marshall Islands", "Mauritania", "Mauritius",
        "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia",
        "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia",
        "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua",
        "Niger", "Nigeria", "North Macedonia", "Norway", "Oman",
        "Pakistan", "Palau", "Palestine", "Panama", "Papua New Guinea",
        "Paraguay", "Peru", "Philippines", "Poland", "Portugal",
        "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis",
        "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa",
        "San Marino", "Sao Tome and Principe", "Saudi Arabia",
        "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore",
        "Slovakia", "Slovenia", "Solomon Islands", "Somalia",
        "South Africa", "South Sudan", "Spain", "Sri Lanka", "Sudan",
        "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan",
        "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga",
        "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan",
        "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates",
        "United Kingdom", "United States", "Uruguay", "Uzbekistan",
        "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen",
        "Zambia", "Zimbabwe"
    ];

    const cities = [
        "New York", "London", "Paris", "Tokyo", "Dubai",
        "Singapore", "Bangkok", "Istanbul", "Kuala Lumpur", "Seoul",
        "Hong Kong", "Shanghai", "Beijing", "Mumbai", "Delhi",
        "Sydney", "Melbourne", "Rome", "Barcelona", "Madrid",
        "Amsterdam", "Berlin", "Vienna", "Prague", "Warsaw",
        "Moscow", "Saint Petersburg", "Cairo", "Johannesburg", "Cape Town",
        "Rio de Janeiro", "São Paulo", "Buenos Aires", "Lima", "Mexico City",
        "Los Angeles", "Chicago", "Miami", "Toronto", "Vancouver",
        "Athens", "Lisbon", "Dublin", "Edinburgh", "Manchester",
        "Brussels", "Zurich", "Geneva", "Oslo", "Stockholm",
        "Helsinki", "Copenhagen", "Reykjavik", "Wellington", "Auckland",
        "Bali", "Phuket", "Hanoi", "Ho Chi Minh City", "Manila",
        "Jakarta", "Colombo", "Kathmandu", "Dhaka", "Yangon",
        "Phnom Penh", "Vientiane", "Ulaanbaatar", "Almaty", "Tashkent",
        "Baku", "Yerevan", "Tbilisi", "Minsk", "Kiev",
        "Bucharest", "Sofia", "Belgrade", "Zagreb", "Sarajevo",
        "Tirana", "Skopje", "Podgorica", "Pristina", "Chisinau",
        "Riga", "Tallinn", "Vilnius", "Bratislava", "Ljubljana",
        "Budapest", "Brasília", "Santiago", "Bogotá", "Caracas",
        "Quito", "La Paz", "Montevideo", "Asunción", "Panama City",
        "San José", "Tegucigalpa", "San Salvador", "Guatemala City", "Managua",
        "Havana", "Kingston", "Port-au-Prince", "Santo Domingo", "Nassau",
        "Bridgetown", "Port of Spain", "Georgetown", "Paramaribo", "Cayenne",
        "Rabat", "Tunis", "Algiers", "Tripoli", "Nouakchott",
        "Bamako", "Ouagadougou", "Niamey", "Conakry", "Dakar",
        "Banjul", "Bissau", "Freetown", "Monrovia", "Abidjan",
        "Accra", "Lomé", "Porto-Novo", "Yaoundé", "Libreville",
        "Brazzaville", "Kinshasa", "Luanda", "Windhoek", "Gaborone",
        "Harare", "Lusaka", "Maputo", "Lilongwe", "Dar es Salaam",
        "Nairobi", "Kampala", "Kigali", "Bujumbura", "Djibouti",
        "Mogadishu", "Addis Ababa", "Asmara", "Khartoum", "Juba",
        "Antananarivo", "Moroni", "Victoria", "Port Louis", "Saint-Denis",
        "Honiara", "Port Vila", "Suva", "Nuku'alofa", "Apia",
        "Majuro", "Tarawa", "Funafuti", "Palikir", "Yaren",
        "Canberra", "Perth", "Adelaide", "Brisbane", "Hobart",
        "Darwin", "Christchurch", "Dunedin", "Queenstown", "Rotorua"
    ];

    const regions = {
        'asia': ['China', 'Japan', 'India', 'Thailand', 'Vietnam', 'Indonesia', 'Malaysia', 'Philippines', 'South Korea', 'Singapore', 'Taiwan', 'Hong Kong', 'Macau', 'Cambodia', 'Laos', 'Myanmar', 'Bangladesh', 'Sri Lanka', 'Nepal', 'Bhutan', 'Maldives', 'Mongolia', 'North Korea', 'Pakistan', 'Afghanistan', 'Iran', 'Iraq', 'Saudi Arabia', 'United Arab Emirates', 'Qatar', 'Oman', 'Kuwait', 'Bahrain', 'Jordan', 'Lebanon', 'Syria', 'Yemen', 'Israel', 'Palestine', 'Turkey', 'Georgia', 'Armenia', 'Azerbaijan', 'Kazakhstan', 'Uzbekistan', 'Turkmenistan', 'Kyrgyzstan', 'Tajikistan'],
        'europe': ['France', 'Italy', 'Spain', 'Germany', 'United Kingdom', 'Greece', 'Portugal', 'Netherlands', 'Switzerland', 'Austria', 'Belgium', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Ireland', 'Poland', 'Czech Republic', 'Hungary', 'Romania', 'Bulgaria', 'Croatia', 'Slovakia', 'Slovenia', 'Lithuania', 'Latvia', 'Estonia', 'Luxembourg', 'Malta', 'Cyprus', 'Iceland', 'Monaco', 'San Marino', 'Vatican City', 'Liechtenstein', 'Andorra', 'Albania', 'Bosnia and Herzegovina', 'Montenegro', 'Serbia', 'North Macedonia', 'Kosovo', 'Moldova', 'Ukraine', 'Belarus', 'Russia'],
        'north-america': ['United States', 'Canada', 'Mexico', 'Greenland', 'Bermuda', 'Saint Pierre and Miquelon'],
        'south-america': ['Brazil', 'Argentina', 'Chile', 'Peru', 'Colombia', 'Venezuela', 'Ecuador', 'Bolivia', 'Paraguay', 'Uruguay', 'Guyana', 'Suriname', 'French Guiana', 'Falkland Islands'],
        'africa': ['South Africa', 'Egypt', 'Morocco', 'Kenya', 'Tanzania', 'Ethiopia', 'Nigeria', 'Ghana', 'Senegal', 'Ivory Coast', 'Cameroon', 'Uganda', 'Rwanda', 'Zambia', 'Zimbabwe', 'Botswana', 'Namibia', 'Mozambique', 'Madagascar', 'Mauritius', 'Seychelles', 'Algeria', 'Tunisia', 'Libya', 'Sudan', 'Democratic Republic of the Congo', 'Republic of the Congo', 'Gabon', 'Angola', 'Malawi', 'Lesotho', 'Eswatini', 'Burundi', 'Djibouti', 'Eritrea', 'Somalia', 'Benin', 'Togo', 'Burkina Faso', 'Mali', 'Niger', 'Chad', 'Central African Republic', 'Equatorial Guinea', 'São Tomé and Príncipe', 'Cape Verde', 'Gambia', 'Guinea-Bissau', 'Sierra Leone', 'Liberia'],
        'oceania': ['Australia', 'New Zealand', 'Fiji', 'Papua New Guinea', 'Solomon Islands', 'Vanuatu', 'Samoa', 'Kiribati', 'Tonga', 'Micronesia', 'Palau', 'Nauru', 'Tuvalu', 'Marshall Islands'],
        'middle-east': ['Saudi Arabia', 'United Arab Emirates', 'Qatar', 'Oman', 'Kuwait', 'Bahrain', 'Jordan', 'Lebanon', 'Syria', 'Yemen', 'Israel', 'Palestine', 'Iraq', 'Iran', 'Turkey', 'Cyprus']
    };

    const categories = ['trending', 'ai-picks', 'beach', 'mountain', 'historical', 'food-culture', 'eco-tourism', 'romantic', 'adventure', 'luxury', 'budget'];
    const moods = ['Relaxed', 'Cultural', 'Adventurous', 'Romantic', 'Eco-Friendly', 'Luxurious', 'Budget', 'Family', 'Solo', 'Business', 'Luxury', 'Backpacker'];
    const icons = ['fa-spa', 'fa-landmark', 'fa-mountain', 'fa-heart', 'fa-leaf', 'fa-crown', 'fa-wallet', 'fa-users', 'fa-user', 'fa-briefcase', 'fa-gem', 'fa-backpack'];

    const categoryIcons = {
        'trending': 'fa-fire',
        'ai-picks': 'fa-robot',
        'beach': 'fa-umbrella-beach',
        'mountain': 'fa-mountain',
        'historical': 'fa-landmark',
        'food-culture': 'fa-utensils',
        'eco-tourism': 'fa-leaf',
        'romantic': 'fa-heart',
        'adventure': 'fa-hiking',
        'luxury': 'fa-crown',
        'budget': 'fa-wallet'
    };

    // Keywords for search
    const activityKeywords = ['skiing', 'hiking', 'diving', 'shopping', 'nightlife', 'family', 'solo', 'spa', 'yoga', 'surfing', 'fishing', 'camping', 'cycling', 'climbing', 'safari', 'cruise', 'golf', 'tennis', 'festival', 'concert'];
    const featureKeywords = ['beach', 'mountain', 'lake', 'river', 'forest', 'desert', 'island', 'city', 'village', 'historic', 'modern', 'luxury', 'budget', 'romantic', 'adventure', 'peaceful', 'crowded', 'remote'];

    for (let i = 1; i <= count; i++) {
        const country = countries[Math.floor(Math.random() * countries.length)];
        const city = cities[Math.floor(Math.random() * cities.length)];
        const price = Math.floor(Math.random() * 4500) + 500;
        const category = categories[Math.floor(Math.random() * categories.length)];
        const moodIndex = Math.floor(Math.random() * moods.length);
        const region = getRegionForCountry(country, regions);

        // Generate keywords for this destination
        const keywords = [
            country.toLowerCase(),
            city.toLowerCase(),
            ...getRandomKeywords(activityKeywords, 2),
            ...getRandomKeywords(featureKeywords, 2),
            moods[moodIndex].toLowerCase(),
            category
        ];

        destinations.push({
            id: i,
            name: `${city}, ${country}`,
            country: country,
            city: city,
            image: getRandomImage(category),
            badge: Math.random() > 0.7 ? category : null,
            badgeIcon: categoryIcons[category],
            price: price,
            mood: moods[moodIndex],
            moodIcon: icons[moodIndex],
            description: getRandomDescription(country, city, category),
            region: region,
            categories: [category, ...getAdditionalCategories(category)],
            rating: (Math.random() * 2 + 3).toFixed(1),
            duration: `${Math.floor(Math.random() * 14) + 3} days`,
            bestTime: getRandomSeason(),
            popularity: Math.floor(Math.random() * 100),
            reviews: Math.floor(Math.random() * 1000) + 100,
            keywords: [...new Set(keywords)], // Remove duplicates
            activities: getRandomActivities(category)
        });
    }

    return destinations;
}

function generateKeywordDatabase() {
    return {
        // Activity keywords
        'skiing': ['ski resort', 'snowboarding', 'winter sports', 'alps', 'mountains'],
        'hiking': ['trekking', 'trails', 'mountains', 'nature', 'outdoor'],
        'diving': ['scuba', 'snorkeling', 'marine life', 'coral reef', 'ocean'],
        'shopping': ['malls', 'markets', 'boutiques', 'fashion', 'retail'],
        'nightlife': ['bars', 'clubs', 'parties', 'entertainment', 'music'],
        'family': ['kids friendly', 'children', 'activities', 'parks', 'safe'],
        'solo': ['single traveler', 'backpacker', 'independent', 'safe', 'hostels'],
        'spa': ['wellness', 'relaxation', 'massage', 'retreat', 'health'],
        'yoga': ['meditation', 'retreat', 'wellness', 'health', 'spiritual'],
        'surfing': ['waves', 'beach', 'ocean', 'water sports', 'coastal'],
        'beach': ['coastal', 'ocean', 'sea', 'sun', 'sand', 'swimming'],
        'mountain': ['alps', 'peaks', 'hiking', 'scenic', 'fresh air'],
        'historical': ['ancient', 'ruins', 'monuments', 'museums', 'culture'],
        'romantic': ['couples', 'honeymoon', 'intimate', 'scenic', 'luxury'],
        'adventure': ['extreme', 'outdoor', 'thrilling', 'exciting', 'active'],
        'luxury': ['5-star', 'premium', 'exclusive', 'high-end', 'VIP'],
        'budget': ['affordable', 'cheap', 'economical', 'hostels', 'backpacker'],

        // City types
        'city': ['urban', 'metropolitan', 'downtown', 'skyscrapers', 'bustling'],
        'village': ['rural', 'countryside', 'traditional', 'quiet', 'local'],

        // Weather/season
        'summer': ['hot', 'sunny', 'beach', 'outdoor', 'festivals'],
        'winter': ['cold', 'snow', 'skiing', 'cozy', 'fireplace'],
        'spring': ['flowers', 'mild', 'blooming', 'fresh', 'outdoor'],
        'autumn': ['fall', 'leaves', 'cool', 'harvest', 'colors']
    };
}

function generateSearchSuggestions() {
    const suggestions = [];

    // Add popular destinations
    const popularDestinations = ['Paris', 'Bali', 'Tokyo', 'New York', 'London', 'Dubai', 'Singapore', 'Bangkok', 'Rome', 'Sydney'];
    popularDestinations.forEach(dest => {
        suggestions.push({
            text: dest,
            type: 'destination',
            icon: 'fa-map-marker-alt'
        });
    });

    // Add popular countries
    const popularCountries = ['Italy', 'Japan', 'Thailand', 'Spain', 'France', 'USA', 'Greece', 'Portugal', 'Vietnam', 'Mexico'];
    popularCountries.forEach(country => {
        suggestions.push({
            text: country,
            type: 'country',
            icon: 'fa-globe'
        });
    });

    // Add activities
    const activities = ['Beach Vacation', 'Mountain Trekking', 'City Tour', 'Cultural Experience', 'Food Tour', 'Adventure Sports', 'Luxury Resort', 'Budget Travel', 'Family Vacation', 'Romantic Getaway'];
    activities.forEach(activity => {
        suggestions.push({
            text: activity,
            type: 'activity',
            icon: 'fa-umbrella-beach'
        });
    });

    searchSuggestions = suggestions;
}

function getRegionForCountry(country, regions) {
    for (const [region, countries] of Object.entries(regions)) {
        if (countries.includes(country)) {
            return region;
        }
    }
    return 'other';
}

function getRandomKeywords(keywordList, count) {
    const shuffled = [...keywordList].sort(() => 0.5 - Math.random());
    return shuffled.slice(0, count);
}

function getRandomActivities(category) {
    const activities = {
        'beach': ['Swimming', 'Sunbathing', 'Water Sports', 'Beach Volleyball', 'Snorkeling'],
        'mountain': ['Hiking', 'Mountain Biking', 'Rock Climbing', 'Camping', 'Skiing'],
        'historical': ['Museum Visits', 'Historical Tours', 'Archaeological Sites', 'Cultural Shows', 'Traditional Workshops'],
        'food-culture': ['Cooking Classes', 'Food Tours', 'Wine Tasting', 'Market Visits', 'Restaurant Hopping'],
        'eco-tourism': ['Wildlife Watching', 'Nature Walks', 'Conservation Activities', 'Eco-friendly Tours', 'Sustainable Farming'],
        'adventure': ['Zip-lining', 'White Water Rafting', 'Bungee Jumping', 'Paragliding', 'Caving'],
        'romantic': ['Sunset Cruises', 'Fine Dining', 'Couples Spa', 'Private Tours', 'Romantic Walks'],
        'luxury': ['Private Villas', 'Helicopter Tours', 'Gourmet Dining', 'Personal Guides', 'Luxury Shopping'],
        'budget': ['Hostel Stays', 'Street Food Tours', 'Free Walking Tours', 'Public Transport', 'Budget Markets']
    };

    return activities[category] || ['Sightseeing', 'Photography', 'Local Culture', 'Shopping', 'Relaxation'];
}

function getRandomImage(category) {
    const images = {
        'beach': 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
        'mountain': 'https://images.unsplash.com/photo-1464278533981-50106e6176b1',
        'historical': 'https://images.unsplash.com/photo-1529264978835-1e7e5f61a1d7',
        'food-culture': 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1',
        'eco-tourism': 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4',
        'romantic': 'https://images.unsplash.com/photo-1518568814500-bf0f8d125f46',
        'adventure': 'https://images.unsplash.com/photo-1536152471326-642d74f114b0',
        'luxury': 'https://images.unsplash.com/photo-1571896349842-33c89424de2d',
        'budget': 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4',
        'hidden': 'https://images.unsplash.com/photo-1552160554-bdfd817add7b',
        'trending': 'https://images.unsplash.com/photo-1488646953014-85cb44e25828',
        'ai-picks': 'https://images.unsplash.com/photo-1488646953014-85cb44e25828'
    };

    const baseUrl = images[category] || 'https://images.unsplash.com/photo-1488646953014-85cb44e25828';
    return `${baseUrl}?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80&t=${Date.now()}`;
}

function getRandomDescription(country, city, category) {
    const descriptions = {
        'beach': `Beautiful beaches and crystal clear waters await in ${city}. Perfect for sunbathing, swimming, and water sports. Enjoy the tropical climate and stunning sunsets.`,
        'mountain': `Breathtaking mountain views and fresh alpine air in ${city}. Ideal for hiking, skiing, and nature lovers. Experience the majestic peaks and serene landscapes.`,
        'historical': `Rich history and ancient architecture in ${city}. Explore centuries-old monuments and cultural heritage. Discover the stories behind historical landmarks.`,
        'food-culture': `Experience authentic ${country} cuisine and vibrant local culture in ${city}. A food lover's paradise with markets, restaurants, and cooking classes.`,
        'eco-tourism': `Sustainable travel experiences in ${city}. Connect with nature while preserving the environment. Enjoy wildlife watching and eco-friendly accommodations.`,
        'romantic': `Romantic getaways and intimate experiences in ${city}. Perfect for couples and special occasions with scenic views and luxury accommodations.`,
        'adventure': `Thrilling adventures and outdoor activities in ${city}. For adrenaline seekers and explorers looking for exciting experiences.`,
        'luxury': `Exclusive luxury experiences and premium accommodations in ${city}. Indulge in the finest travel with personalized services.`,
        'budget': `Affordable travel options and great value in ${city}. Experience amazing destinations without breaking the bank.`,
        'trending': `Currently trending destination! ${city} offers unique experiences that are popular right now with travelers worldwide.`,
        'ai-picks': `AI-recommended hidden gem! ${city} offers unique experiences based on your preferences with high satisfaction ratings.`
    };

    return descriptions[category] || `Experience the beauty and culture of ${city}, ${country}. Explore local attractions, enjoy delicious cuisine, and create unforgettable memories.`;
}

function getAdditionalCategories(mainCategory) {
    const additional = [];
    const allCategories = ['trending', 'ai-picks', 'beach', 'mountain', 'historical', 'food-culture', 'eco-tourism', 'romantic', 'adventure', 'luxury', 'budget'];

    // Add 1-2 random additional categories
    const numAdditional = Math.floor(Math.random() * 2) + 1;
    for (let i = 0; i < numAdditional; i++) {
        let randomCat;
        do {
            randomCat = allCategories[Math.floor(Math.random() * allCategories.length)];
        } while (randomCat === mainCategory || additional.includes(randomCat));
        additional.push(randomCat);
    }

    return additional;
}

function getRandomSeason() {
    const seasons = ['Spring (Mar-May)', 'Summer (Jun-Aug)', 'Autumn (Sep-Nov)', 'Winter (Dec-Feb)', 'Year-round'];
    return seasons[Math.floor(Math.random() * seasons.length)];
}

function generateFeaturedDestinations(count) {
    const featured = [];
    const hiddenGems = [
        { name: "Kotor, Montenegro", country: "Montenegro", match: 94 },
        { name: "Chiang Mai, Thailand", country: "Thailand", match: 91 },
        { name: "Azores, Portugal", country: "Portugal", match: 89 },
        { name: "Luang Prabang, Laos", country: "Laos", match: 87 },
        { name: "Salzburg, Austria", country: "Austria", match: 85 },
        { name: "Siem Reap, Cambodia", country: "Cambodia", match: 83 },
        { name: "Zanzibar, Tanzania", country: "Tanzania", match: 82 },
        { name: "Queenstown, New Zealand", country: "New Zealand", match: 80 },
        { name: "Hallstatt, Austria", country: "Austria", match: 88 },
        { name: "Bagan, Myanmar", country: "Myanmar", match: 86 },
        { name: "Cappadocia, Turkey", country: "Turkey", match: 84 },
        { name: "Meteora, Greece", country: "Greece", match: 82 },
        { name: "Plitvice Lakes, Croatia", country: "Croatia", match: 80 },
        { name: "Santorini, Greece", country: "Greece", match: 78 },
        { name: "Machu Picchu, Peru", country: "Peru", match: 76 }
    ];

    for (let i = 0; i < Math.min(count, hiddenGems.length); i++) {
        const gem = hiddenGems[i];
        featured.push({
            id: i + 1000,
            name: gem.name,
            country: gem.country,
            image: getRandomImage('hidden'),
            description: `Discover the hidden beauty of ${gem.name.split(',')[0]} with unique cultural experiences and breathtaking landscapes.`,
            match: gem.match,
            keywords: [gem.country.toLowerCase(), gem.name.split(',')[0].toLowerCase(), 'hidden gem', 'offbeat', 'unique']
        });
    }

    return featured;
}

// Wishlist functionality
function updateWishlistCount() {
    const countElement = document.getElementById('wishlistCount');
    if (countElement) {
        countElement.textContent = wishlistCount;
        countElement.style.display = wishlistCount > 0 ? 'flex' : 'none';
    }
}

function toggleWishlist(destinationId, element = null) {
    const destination = [...destinationsData, ...featuredData].find(d => d.id === destinationId);
    if (!destination) return;

    const index = wishlist.indexOf(destinationId);

    if (index === -1) {
        // Add to wishlist
        wishlist.push(destinationId);
        wishlistCount++;
        if (element) {
            element.classList.add('active');
            element.innerHTML = '<i class="fas fa-heart"></i>';
            showNotification(`${destination.name} added to wishlist!`, 'success');
        }
    } else {
        // Remove from wishlist
        wishlist.splice(index, 1);
        wishlistCount--;
        if (element) {
            element.classList.remove('active');
            element.innerHTML = '<i class="far fa-heart"></i>';
            showNotification(`${destination.name} removed from wishlist!`, 'info');
        }
    }

    // Save to localStorage
    localStorage.setItem('smartBookingWishlist', JSON.stringify(wishlist));
    updateWishlistCount();

    // Update all wishlist buttons on the page
    updateWishlistButtons();

    // Sync with server if CSRF token exists
    if (csrfToken) {
        syncWishlistWithServer(destinationId, index === -1 ? 'add' : 'remove');
    }
}

function syncWishlistWithServer(destinationId, action) {
    // This would be your actual API endpoint
    console.log(`Syncing wishlist: ${action} destination ${destinationId} with server`);
    // fetch('/api/wishlist', {
    //     method: action === 'add' ? 'POST' : 'DELETE',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': csrfToken
    //     },
    //     body: JSON.stringify({ destination_id: destinationId })
    // }).catch(error => {
    //     console.error('Failed to sync wishlist:', error);
    // });
}

function isInWishlist(destinationId) {
    return wishlist.includes(destinationId);
}

function updateWishlistButtons() {
    document.querySelectorAll('.wishlist-btn').forEach(button => {
        const destinationId = parseInt(button.getAttribute('data-id'));
        if (isInWishlist(destinationId)) {
            button.classList.add('active');
            button.innerHTML = '<i class="fas fa-heart"></i>';
        } else {
            button.classList.remove('active');
            button.innerHTML = '<i class="far fa-heart"></i>';
        }
    });
}

// Setup wishlist navigation
function setupWishlistNavigation() {
    const wishlistLink = document.getElementById('wishlistLink');
    if (wishlistLink) {
        wishlistLink.addEventListener('click', function(e) {
            if (wishlistCount === 0) {
                e.preventDefault();
                showNotification('Your wishlist is empty! Add some destinations first.', 'info');
            }
        });
    }
}

// Core functionality
function loadDestinations() {
    showLoading();

    setTimeout(() => {
        filteredDestinations = destinationsData.filter(destination => {
            // Apply category filter
            if (activeFilters.category !== 'all' && !destination.categories.includes(activeFilters.category)) {
                return false;
            }

            // Apply region filter
            if (activeFilters.region !== 'all' && destination.region !== activeFilters.region) {
                return false;
            }

            // Apply price filter
            if (destination.price > activeFilters.price) {
                return false;
            }

            // Apply search filter
            if (activeFilters.search) {
                const searchTerm = activeFilters.search.toLowerCase();
                const searchWords = searchTerm.split(' ').filter(word => word.length > 0);

                // Check if any search word matches
                let hasMatch = false;
                for (const word of searchWords) {
                    // Check name, country, city
                    if (destination.name.toLowerCase().includes(word) ||
                        destination.country.toLowerCase().includes(word) ||
                        destination.city.toLowerCase().includes(word) ||
                        destination.mood.toLowerCase().includes(word) ||
                        destination.description.toLowerCase().includes(word)) {
                        hasMatch = true;
                        break;
                    }

                    // Check keywords
                    if (destination.keywords && destination.keywords.some(keyword => keyword.includes(word))) {
                        hasMatch = true;
                        break;
                    }

                    // Check activities
                    if (destination.activities && destination.activities.some(activity =>
                        activity.toLowerCase().includes(word))) {
                        hasMatch = true;
                        break;
                    }

                    // Check category aliases from keyword database
                    if (keywordDatabase[word]) {
                        const aliases = keywordDatabase[word];
                        if (aliases.some(alias =>
                            destination.keywords.some(keyword => keyword.includes(alias)) ||
                            destination.description.toLowerCase().includes(alias))) {
                            hasMatch = true;
                            break;
                        }
                    }
                }

                if (!hasMatch) return false;
            }

            // Apply keyword filters
            if (activeFilters.keywords.length > 0) {
                const destinationKeywords = [
                    ...destination.keywords,
                    destination.mood.toLowerCase(),
                    ...destination.categories,
                    ...destination.activities.map(a => a.toLowerCase())
                ];

                const hasAllKeywords = activeFilters.keywords.every(keyword =>
                    destinationKeywords.some(destKeyword =>
                        destKeyword.includes(keyword.toLowerCase())
                    )
                );

                if (!hasAllKeywords) return false;
            }

            return true;
        });

        // Apply sorting
        applySorting();

        // Render
        renderDestinations();
        renderPagination();
        hideLoading();

        // Update UI
        updateResultsInfo();
        updateCurrentFilterText();
        updateActiveFiltersDisplay();

        // Show/hide no results
        if (filteredDestinations.length === 0) {
            document.getElementById('noResults').style.display = 'block';
            document.getElementById('destinationsGrid').style.display = 'none';
            document.getElementById('pagination').style.display = 'none';
        } else {
            document.getElementById('noResults').style.display = 'none';
            document.getElementById('destinationsGrid').style.display = 'grid';
            document.getElementById('pagination').style.display = 'flex';
        }
    }, 300);
}

function applySorting() {
    switch(currentSort) {
        case 'price-low':
            filteredDestinations.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filteredDestinations.sort((a, b) => b.price - a.price);
            break;
        case 'name':
            filteredDestinations.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'rating':
            filteredDestinations.sort((a, b) => parseFloat(b.rating) - parseFloat(a.rating));
            break;
        case 'reviews':
            filteredDestinations.sort((a, b) => b.reviews - a.reviews);
            break;
        case 'popular':
        default:
            filteredDestinations.sort((a, b) => b.popularity - a.popularity);
            break;
    }
}

function renderDestinations() {
    const grid = document.getElementById('destinationsGrid');
    grid.innerHTML = '';

    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
    const endIndex = startIndex + ITEMS_PER_PAGE;
    const pageDestinations = filteredDestinations.slice(startIndex, endIndex);

    if (pageDestinations.length === 0) return;

    pageDestinations.forEach(destination => {
        const badge = destination.badge ? getBadgeHtml(destination.badge, destination.badgeIcon) : '';
        const isWishlisted = isInWishlist(destination.id);

        const card = document.createElement('div');
        card.className = 'destination-card';
        card.innerHTML = `
            <div class="destination-image" style="background-image:url('${destination.image}')">
                ${badge}
                <button class="wishlist-btn ${isWishlisted ? 'active' : ''}"
                        data-id="${destination.id}"
                        onclick="toggleWishlist(${destination.id}, this)"
                        aria-label="${isWishlisted ? 'Remove from' : 'Add to'} wishlist">
                    <i class="${isWishlisted ? 'fas' : 'far'} fa-heart"></i>
                </button>
                <div class="dest-badge" style="top: auto; bottom: 15px; right: 15px; left: auto; background: var(--gold);">
                    <i class="fas fa-star"></i> ${destination.rating}
                </div>
            </div>
            <div class="destination-content">
                <h3>${destination.name}</h3>
                <div class="destination-meta">
                    <span class="price-tag">
                        <i class="fas fa-dollar-sign"></i> ${destination.price.toLocaleString()}+
                    </span>
                    <span class="mood-indicator">
                        <i class="fas ${destination.moodIcon}"></i> ${destination.mood}
                    </span>
                </div>
                <p>${destination.description}</p>
                <div class="destination-footer">
                    <button class="primary-button" onclick="showDestinationDetails(${destination.id})" aria-label="View details for ${destination.name}">
                        <i class="fas fa-info-circle"></i> Details
                    </button>
                    <button class="secondary-button" onclick="toggleWishlist(${destination.id})" aria-label="${isWishlisted ? 'Remove from' : 'Add to'} wishlist">
                        <i class="${isWishlisted ? 'fas' : 'far'} fa-heart"></i>
                        ${isWishlisted ? 'Saved' : 'Save'}
                    </button>
                </div>
            </div>
        `;

        grid.appendChild(card);
    });

    // Initialize lazy loading for images
    initImageLazyLoading();
}

function getBadgeHtml(badgeType, icon) {
    const badges = {
        'trending': { text: 'Trending', icon: 'fa-fire' },
        'ai-picks': { text: 'AI Pick', icon: 'fa-robot' },
        'beach': { text: 'Beach', icon: 'fa-umbrella-beach' },
        'mountain': { text: 'Mountain', icon: 'fa-mountain' },
        'historical': { text: 'Historical', icon: 'fa-landmark' },
        'food-culture': { text: 'Food & Culture', icon: 'fa-utensils' },
        'eco-tourism': { text: 'Eco-Tourism', icon: 'fa-leaf' },
        'romantic': { text: 'Romantic', icon: 'fa-heart' },
        'adventure': { text: 'Adventure', icon: 'fa-hiking' },
        'luxury': { text: 'Luxury', icon: 'fa-crown' },
        'budget': { text: 'Budget', icon: 'fa-wallet' }
    };

    const badge = badges[badgeType];
    if (!badge) return '';

    return `<span class="dest-badge">
                <i class="fas ${icon || badge.icon}"></i> ${badge.text}
            </span>`;
}

function renderPagination() {
    const pagination = document.getElementById('pagination');
    const totalPages = Math.ceil(filteredDestinations.length / ITEMS_PER_PAGE);

    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let html = '';

    // Previous button
    if (currentPage > 1) {
        html += `<button class="page-btn" onclick="changePage(${currentPage - 1})" aria-label="Previous page">
                    <i class="fas fa-chevron-left"></i> Previous
                 </button>`;
    }

    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    // Show first page if not already visible
    if (startPage > 1) {
        html += `<button class="page-btn" onclick="changePage(1)">1</button>`;
        if (startPage > 2) {
            html += `<span class="page-btn" style="cursor: default; background: transparent; border: none;">...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}"
                        onclick="changePage(${i})"
                        aria-label="${i === currentPage ? 'Current page, ' : ''}Page ${i}"
                        ${i === currentPage ? 'aria-current="page"' : ''}>
                    ${i}
                </button>`;
    }

    // Show last page if not already visible
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="page-btn" style="cursor: default; background: transparent; border: none;">...</span>`;
        }
        html += `<button class="page-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
    }

    // Next button
    if (currentPage < totalPages) {
        html += `<button class="page-btn" onclick="changePage(${currentPage + 1})" aria-label="Next page">
                    Next <i class="fas fa-chevron-right"></i>
                 </button>`;
    }

    pagination.innerHTML = html;
}

function loadFeaturedDestinations() {
    const grid = document.getElementById('featuredGrid');
    grid.innerHTML = '';

    featuredData.forEach(featured => {
        const isWishlisted = isInWishlist(featured.id);
        const card = document.createElement('div');
        card.className = 'featured-card';
        card.tabIndex = 0;
        card.onclick = () => showDestinationDetails(featured.id);
        card.onkeypress = (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                showDestinationDetails(featured.id);
            }
        };
        card.innerHTML = `
            <div class="feat-img" style="background-image:url('${featured.image}')"></div>
            <div class="feat-body">
                <h4>${featured.name}</h4>
                <p>${featured.description}</p>
                <span class="feat-tag">
                    <i class="fas fa-percentage"></i> ${featured.match}% Match
                </span>
                <button class="wishlist-btn ${isWishlisted ? 'active' : ''}"
                        style="position: absolute; top: 10px; right: 10px;"
                        data-id="${featured.id}"
                        onclick="event.stopPropagation(); toggleWishlist(${featured.id}, this)"
                        aria-label="${isWishlisted ? 'Remove from' : 'Add to'} wishlist">
                    <i class="${isWishlisted ? 'fas' : 'far'} fa-heart"></i>
                </button>
            </div>
        `;

        grid.appendChild(card);
    });
}

function showDestinationDetails(id) {
    const destination = [...destinationsData, ...featuredData].find(d => d.id === id);
    if (!destination) return;

    const modal = document.getElementById('destinationModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');

    modalTitle.textContent = destination.name;

    const isWishlisted = isInWishlist(id);

    modalBody.innerHTML = `
        <div class="destination-detail">
            <div class="detail-image" style="background-image:url('${destination.image}')">
                <button class="wishlist-btn ${isWishlisted ? 'active' : ''}"
                        style="top: 15px; right: 15px;"
                        data-id="${destination.id}"
                        onclick="toggleWishlist(${destination.id}, this)"
                        aria-label="${isWishlisted ? 'Remove from' : 'Add to'} wishlist">
                    <i class="${isWishlisted ? 'fas' : 'far'} fa-heart"></i>
                </button>
            </div>
            <div class="detail-info">
                <h3>${destination.name}</h3>
                <div class="detail-meta">
                    <span class="price-tag">
                        <i class="fas fa-dollar-sign"></i> ${destination.price?.toLocaleString() || '1,200'}+
                    </span>
                    <span class="mood-indicator">
                        <i class="fas ${destination.moodIcon || 'fa-globe'}"></i>
                        ${destination.mood || 'Cultural'}
                    </span>
                    <span class="mood-indicator">
                        <i class="fas fa-calendar-alt"></i>
                        ${destination.duration || '7 days'}
                    </span>
                    <span class="mood-indicator">
                        <i class="fas fa-star"></i>
                        ${destination.rating || '4.5'} / 5
                    </span>
                </div>

                <div class="detail-description">
                    <p>${destination.description || 'Experience the beauty and culture of this amazing destination.'}</p>
                    <p><i class="fas fa-calendar"></i> Best time to visit: <strong>${destination.bestTime || 'Year-round'}</strong></p>
                    <p><i class="fas fa-comments"></i> Reviews: <strong>${destination.reviews || '250'}+ positive reviews</strong></p>
                    <p><i class="fas fa-hiking"></i> Activities: <strong>${destination.activities ? destination.activities.join(', ') : 'Various activities available'}</strong></p>
                </div>

                <div class="detail-features">
                    <div class="feature-item">
                        <i class="fas fa-utensils"></i>
                        <span>Local Cuisine</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-camera"></i>
                        <span>Photo Opportunities</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-hiking"></i>
                        <span>Outdoor Activities</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-history"></i>
                        <span>Cultural Heritage</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-wifi"></i>
                        <span>Free WiFi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-swimming-pool"></i>
                        <span>Pool Available</span>
                    </div>
                </div>

                <div class="export-options">
                    <button class="export-btn" onclick="exportDestination(${destination.id}, 'print')" aria-label="Print destination details">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="export-btn" onclick="shareDestination(${destination.id})" aria-label="Share destination">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                </div>

                <div class="modal-actions">
                    <button class="primary-button" style="flex: 2;" onclick="planTrip(${destination.id})" aria-label="Plan trip to ${destination.name}">
                        <i class="fas fa-route"></i> Plan Trip
                    </button>
                    <button class="secondary-button" style="flex: 1;" onclick="toggleWishlist(${destination.id})" aria-label="${isWishlisted ? 'Remove from' : 'Add to'} wishlist">
                        <i class="${isWishlisted ? 'fas' : 'far'} fa-heart"></i> ${isWishlisted ? 'Saved' : 'Save'}
                    </button>
                </div>
            </div>
        </div>
    `;

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Set focus to the first focusable element in modal
    setTimeout(() => {
        const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) firstFocusable.focus();
    }, 100);
}

function setupEventListeners() {
    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            activeFilters.category = this.getAttribute('data-filter');
            currentPage = 1;
            loadDestinations();
            trackInteraction('filter_category', activeFilters.category);
        });
    });

    // Region pills
    document.querySelectorAll('.region-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.region-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            activeFilters.region = this.getAttribute('data-region');
            currentPage = 1;
            loadDestinations();
            trackInteraction('filter_region', activeFilters.region);
        });
    });

    // Quick filters
    document.querySelectorAll('.quick-filter').forEach(filter => {
        filter.addEventListener('click', function() {
            const keyword = this.getAttribute('data-keyword');
            this.classList.toggle('active');

            if (this.classList.contains('active')) {
                if (!activeFilters.keywords.includes(keyword)) {
                    activeFilters.keywords.push(keyword);
                }
            } else {
                const index = activeFilters.keywords.indexOf(keyword);
                if (index > -1) {
                    activeFilters.keywords.splice(index, 1);
                }
            }

            currentPage = 1;
            loadDestinations();
            trackInteraction('filter_keyword', keyword);
        });
    });

    // Price slider
    const priceSlider = document.getElementById('priceSlider');
    if (priceSlider) {
        priceSlider.addEventListener('input', function() {
            activeFilters.price = parseInt(this.value);
            document.getElementById('priceValue').textContent = `$${this.value}+`;
            currentPage = 1;
            loadDestinations();
            trackInteraction('filter_price', activeFilters.price);
        });
    }

    // Sort select
    document.getElementById('sortSelect').addEventListener('change', function() {
        currentSort = this.value;
        loadDestinations();
        trackInteraction('sort', currentSort);
    });

    // Search input with debounce
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const searchSuggestions = document.getElementById('searchSuggestions');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (query.length > 0) {
                showSearchSuggestions(query);
            } else {
                hideSearchSuggestions();
            }
        }, 300);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            showSearchSuggestions(this.value.trim());
        }
    });

    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') performSearch();
    });

    // Close search suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
            hideSearchSuggestions();
        }
    });

    // Reset button
    document.getElementById('resetBtn').addEventListener('click', resetFilters);

    // Modal close
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('destinationModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Add keyboard event for region pills and quick filters
    document.querySelectorAll('.region-pill, .quick-filter').forEach(element => {
        element.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
}

function showSearchSuggestions(query) {
    const suggestionsDiv = document.getElementById('searchSuggestions');
    const queryLower = query.toLowerCase();

    // Filter suggestions
    const filtered = searchSuggestions.filter(suggestion =>
        suggestion.text.toLowerCase().includes(queryLower)
    ).slice(0, 8); // Limit to 8 suggestions

    if (filtered.length === 0) {
        suggestionsDiv.style.display = 'none';
        return;
    }

    // Build suggestions HTML
    let html = '';
    filtered.forEach((suggestion, index) => {
        html += `
            <div class="suggestion-item"
                 onclick="selectSuggestion('${suggestion.text.replace(/'/g, "\\'")}')"
                 onkeypress="if(event.key === 'Enter') selectSuggestion('${suggestion.text.replace(/'/g, "\\'")}')"
                 tabindex="0"
                 role="option"
                 ${index === 0 ? 'id="first-suggestion"' : ''}>
                <i class="fas ${suggestion.icon}"></i>
                <span>${suggestion.text}</span>
                <span style="margin-left: auto; font-size: 11px; color: var(--text-muted);">
                    ${suggestion.type}
                </span>
            </div>
        `;
    });

    suggestionsDiv.innerHTML = html;
    suggestionsDiv.style.display = 'block';
}

function hideSearchSuggestions() {
    document.getElementById('searchSuggestions').style.display = 'none';
}

function selectSuggestion(text) {
    document.getElementById('searchInput').value = text;
    hideSearchSuggestions();
    performSearch();
}

function performSearch() {
    activeFilters.search = document.getElementById('searchInput').value.trim();
    currentPage = 1;
    loadDestinations();
    hideSearchSuggestions();
    trackInteraction('search', activeFilters.search);
}

function resetFilters() {
    // Reset active filters
    activeFilters = {
        category: 'all',
        region: 'all',
        price: 5000,
        keywords: [],
        search: ''
    };

    // Reset UI
    document.getElementById('searchInput').value = '';
    document.getElementById('priceSlider').value = 5000;
    document.getElementById('priceValue').textContent = '$5000+';
    document.getElementById('sortSelect').value = 'popular';

    // Reset filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.toggle('active', tab.getAttribute('data-filter') === 'all');
    });

    // Reset region pills
    document.querySelectorAll('.region-pill').forEach(pill => {
        pill.classList.toggle('active', pill.getAttribute('data-region') === 'all');
    });

    // Reset quick filters
    document.querySelectorAll('.quick-filter').forEach(filter => {
        filter.classList.remove('active');
    });

    currentSort = 'popular';
    currentPage = 1;
    loadDestinations();
    trackInteraction('reset_filters');
}

function applyFilterPreset(presetName) {
    const preset = filterPresets[presetName];
    if (preset) {
        // Reset current filters
        resetFilters();

        // Apply preset
        activeFilters = { ...preset };

        // Update UI
        document.getElementById('priceSlider').value = preset.price;
        document.getElementById('priceValue').textContent = `$${preset.price}+`;

        // Update active category tab if preset has one
        if (preset.category !== 'all') {
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.toggle('active', tab.getAttribute('data-filter') === preset.category);
            });
        }

        // Update quick filters
        document.querySelectorAll('.quick-filter').forEach(filter => {
            const keyword = filter.getAttribute('data-keyword');
            filter.classList.toggle('active', preset.keywords.includes(keyword));
        });

        currentPage = 1;
        loadDestinations();
        trackInteraction('filter_preset', presetName);
        showNotification(`Applied ${presetName.replace(/([A-Z])/g, ' $1').trim()} preset`, 'success');
    }
}

function updatePriceSlider() {
    const slider = document.getElementById('priceSlider');
    const value = document.getElementById('priceValue');
    if (slider && value) {
        value.textContent = `$${slider.value}+`;
    }
}

function updateActiveFiltersDisplay() {
    const container = document.getElementById('activeFilters');
    let html = '';

    // Add category filter
    if (activeFilters.category !== 'all') {
        html += `<div class="active-filter-tag">
                    <span>Category: ${activeFilters.category}</span>
                    <i class="fas fa-times" onclick="removeFilter('category')" role="button" tabindex="0" aria-label="Remove category filter"></i>
                 </div>`;
    }

    // Add region filter
    if (activeFilters.region !== 'all') {
        html += `<div class="active-filter-tag">
                    <span>Region: ${activeFilters.region}</span>
                    <i class="fas fa-times" onclick="removeFilter('region')" role="button" tabindex="0" aria-label="Remove region filter"></i>
                 </div>`;
    }

    // Add price filter
    if (activeFilters.price < 5000) {
        html += `<div class="active-filter-tag">
                    <span>Max Price: $${activeFilters.price}+</span>
                    <i class="fas fa-times" onclick="removeFilter('price')" role="button" tabindex="0" aria-label="Remove price filter"></i>
                 </div>`;
    }

    // Add keyword filters
    activeFilters.keywords.forEach(keyword => {
        html += `<div class="active-filter-tag">
                    <span>${keyword.charAt(0).toUpperCase() + keyword.slice(1)}</span>
                    <i class="fas fa-times" onclick="removeKeywordFilter('${keyword}')" role="button" tabindex="0" aria-label="Remove ${keyword} filter"></i>
                 </div>`;
    });

    // Add search filter
    if (activeFilters.search) {
        html += `<div class="active-filter-tag">
                    <span>Search: "${activeFilters.search}"</span>
                    <i class="fas fa-times" onclick="removeFilter('search')" role="button" tabindex="0" aria-label="Remove search filter"></i>
                 </div>`;
    }

    container.innerHTML = html;
}

function removeFilter(type) {
    switch(type) {
        case 'category':
            activeFilters.category = 'all';
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.toggle('active', tab.getAttribute('data-filter') === 'all');
            });
            break;
        case 'region':
            activeFilters.region = 'all';
            document.querySelectorAll('.region-pill').forEach(pill => {
                pill.classList.toggle('active', pill.getAttribute('data-region') === 'all');
            });
            break;
        case 'price':
            activeFilters.price = 5000;
            document.getElementById('priceSlider').value = 5000;
            document.getElementById('priceValue').textContent = '$5000+';
            break;
        case 'search':
            activeFilters.search = '';
            document.getElementById('searchInput').value = '';
            break;
    }

    currentPage = 1;
    loadDestinations();
    trackInteraction('remove_filter', type);
}

function removeKeywordFilter(keyword) {
    const index = activeFilters.keywords.indexOf(keyword);
    if (index > -1) {
        activeFilters.keywords.splice(index, 1);
    }

    // Update UI
    document.querySelectorAll('.quick-filter').forEach(filter => {
        if (filter.getAttribute('data-keyword') === keyword) {
            filter.classList.remove('active');
        }
    });

    currentPage = 1;
    loadDestinations();
    trackInteraction('remove_keyword_filter', keyword);
}

function changePage(page) {
    currentPage = page;
    loadDestinations();
    window.scrollTo({ top: 400, behavior: 'smooth' });
    trackInteraction('page_change', page);
}

function closeModal() {
    document.getElementById('destinationModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    // Return focus to the element that opened the modal
    const lastFocused = document.activeElement;
    if (lastFocused) lastFocused.focus();
}

function updateResultsInfo() {
    const total = filteredDestinations.length;
    const start = (currentPage - 1) * ITEMS_PER_PAGE + 1;
    const end = Math.min(currentPage * ITEMS_PER_PAGE, total);

    document.getElementById('resultsCount').textContent = `${start}-${end}`;
}

function updateTotalCount() {
    document.getElementById('totalCount').textContent = destinationsData.length.toLocaleString();
}

function updateCurrentFilterText() {
    const filterText = document.getElementById('currentFilter');
    let text = '';

    if (activeFilters.category !== 'all') {
        const filterIcons = {
            'trending': 'fa-fire',
            'ai-picks': 'fa-robot',
            'beach': 'fa-umbrella-beach',
            'mountain': 'fa-mountain',
            'historical': 'fa-landmark',
            'food-culture': 'fa-utensils',
            'eco-tourism': 'fa-leaf',
            'romantic': 'fa-heart',
            'adventure': 'fa-hiking',
            'luxury': 'fa-crown',
            'budget': 'fa-wallet'
        };
        text += `<i class="fas ${filterIcons[activeFilters.category]}"></i> ${activeFilters.category.charAt(0).toUpperCase() + activeFilters.category.slice(1)}`;
    }

    if (activeFilters.region !== 'all') {
        if (text) text += ' • ';
        const regionIcons = {
            'asia': 'fa-globe-asia',
            'europe': 'fa-globe-europe',
            'north-america': 'fa-globe-americas',
            'south-america': 'fa-globe-americas',
            'africa': 'fa-globe-africa',
            'oceania': 'fa-globe-asia',
            'middle-east': 'fa-mosque'
        };
        const regionNames = {
            'asia': 'Asia',
            'europe': 'Europe',
            'north-america': 'North America',
            'south-america': 'South America',
            'africa': 'Africa',
            'oceania': 'Oceania',
            'middle-east': 'Middle East'
        };
        text += `<i class="fas ${regionIcons[activeFilters.region]}"></i> ${regionNames[activeFilters.region] || activeFilters.region}`;
    }

    filterText.innerHTML = text;
}

function showLoading() {
    document.getElementById('loading').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.setAttribute('role', 'alert');
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" aria-label="Close notification"><i class="fas fa-times"></i></button>
    `;

    document.body.appendChild(notification);

    // Remove notification after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

// Track user interactions
function trackInteraction(action, data = null) {
    console.log(`User interaction: ${action}`, data);
    // In production, send to analytics service
    // Example: sendToAnalytics({ action, data, timestamp: new Date().toISOString() });
}

// Image lazy loading
function initImageLazyLoading() {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.style.backgroundImage = `url('${img.dataset.src}')`;
                    img.removeAttribute('data-src');
                }
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px 0px',
        threshold: 0.1
    });

    // Convert images to lazy load
    document.querySelectorAll('.destination-image, .feat-img').forEach(img => {
        const currentBg = img.style.backgroundImage;
        if (currentBg && currentBg !== 'none') {
            const url = currentBg.match(/url\(["']?(.+?)["']?\)/)[1];
            img.dataset.src = url;
            img.style.backgroundImage = 'none';
            img.style.backgroundColor = 'var(--border)';
            imageObserver.observe(img);
        }
    });
}

// Keyboard navigation
function initKeyboardNavigation() {
    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('destinationModal');

        if (e.key === 'Escape') {
            if (modal.style.display === 'block') {
                closeModal();
            }
            hideSearchSuggestions();
        }

        if (e.key === 'Tab' && modal.style.display === 'block') {
            trapFocus(modal, e);
        }

        // Navigate search suggestions with arrow keys
        if (document.activeElement === document.getElementById('searchInput')) {
            const suggestions = document.getElementById('searchSuggestions');
            if (suggestions.style.display === 'block') {
                const suggestionItems = suggestions.querySelectorAll('.suggestion-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (suggestionItems.length > 0) {
                        suggestionItems[0].focus();
                    }
                }
            }
        }
    });
}

function trapFocus(element, event) {
    const focusableElements = element.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === firstFocusable) {
        lastFocusable.focus();
        event.preventDefault();
    } else if (!event.shiftKey && document.activeElement === lastFocusable) {
        firstFocusable.focus();
        event.preventDefault();
    }
}

// Action functions
function planTrip(id) {
    const destination = [...destinationsData, ...featuredData].find(d => d.id === id);
    if (destination) {
        showNotification(`Planning a trip to ${destination.name}!`, 'success');
        closeModal();
        trackInteraction('plan_trip', destination.id);
        // In real app: window.location.href = `/plan-trip?destination=${id}`;
    }
}

function shareDestination(id) {
    const destination = [...destinationsData, ...featuredData].find(d => d.id === id);
    if (destination) {
        if (navigator.share) {
            navigator.share({
                title: `Check out ${destination.name} on Smart Booking`,
                text: `I found this amazing destination on Smart Booking: ${destination.name}`,
                url: window.location.href
            }).then(() => {
                trackInteraction('share_success', destination.id);
            }).catch(error => {
                console.log('Sharing cancelled:', error);
            });
        } else {
            // Fallback for desktop
            const shareUrl = `https://smartbooking.com/destination/${id}`;
            navigator.clipboard.writeText(shareUrl).then(() => {
                showNotification('Share link copied to clipboard!', 'success');
                trackInteraction('share_copy', destination.id);
            });
        }
    }
}

function exportDestination(id, format = 'print') {
    const destination = [...destinationsData, ...featuredData].find(d => d.id === id);
    if (!destination) return;

    if (format === 'print') {
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>${destination.name} - Smart Booking</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h1 { color: #3b1f2b; }
                    .meta { display: flex; gap: 15px; margin: 20px 0; }
                    .meta-item { background: #f5f0eb; padding: 10px; border-radius: 4px; }
                    .description { margin: 20px 0; line-height: 1.6; }
                </style>
            </head>
            <body>
                <h1>${destination.name}</h1>
                <div class="meta">
                    <div class="meta-item">Price: $${destination.price?.toLocaleString() || '1,200'}+</div>
                    <div class="meta-item">Rating: ${destination.rating || '4.5'}/5</div>
                    <div class="meta-item">Duration: ${destination.duration || '7 days'}</div>
                </div>
                <div class="description">
                    <p>${destination.description}</p>
                    <p><strong>Best time to visit:</strong> ${destination.bestTime || 'Year-round'}</p>
                    <p><strong>Activities:</strong> ${destination.activities ? destination.activities.join(', ') : 'Various activities available'}</p>
                </div>
                <p style="margin-top: 30px; color: #666; font-size: 12px;">
                    Printed from Smart Booking - ${new Date().toLocaleDateString()}
                </p>
            </body>
            </html>
        `;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();

        trackInteraction('export_print', destination.id);
    }
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('Error occurred:', e.error);
    showNotification('An error occurred. Please try again.', 'info');
});

// Service worker registration for offline support
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch(error => {
            console.log('Service Worker registration failed:', error);
        });
    });
}
</script>
</body>
</html>
