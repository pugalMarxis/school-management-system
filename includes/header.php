<?php
if(!isset($_SESSION)) { session_start(); }
if(!isset($_SESSION['user_role'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | EduManage Management Suite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-navy: #1e3a8a;
            --accent-blue: #3b82f6;
            --dark-slate: #0f172a;
            --bg-light: #f8fafc;
            --sidebar-width: 260px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            overflow-x: hidden;
        }
        #wrapper {
            display: flex;
            width: 100%;
        }
        #sidebar-wrapper {
            min-height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--dark-slate);
            transition: all 0.3s ease;
        }
        #page-content-wrapper {
            width: 100%;
            flex-grow: 1;
        }
        .sidebar-heading {
            padding: 1.5rem 1.25rem;
            font-size: 1.2rem;
            color: #fff;
            background-color: #1e293b;
        }
        .list-group-item-action {
            color: #94a3b8;
            transition: all 0.2s ease;
        }
        .list-group-item-action:hover, .list-group-item-action.active {
            color: #fff;
            background-color: var(--primary-navy) !important;
            border-color: var(--primary-navy) !important;
        }
        .top-navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
    </style>
</head>
<body>
    <div id="wrapper">