<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case VIEW_DASHBOARD = 'view_dashboard';
    case MANAGE_USERS = 'manage_users';
    case VIEW_REPORTS = 'view_reports';
    case MANAGE_SETTINGS = 'manage_settings';
    case MANAGE_CONTENT = 'manage_content';

    // Master Class Permissions
    case VIEW_MASTER_CLASS = 'view_master_class';
    case CREATE_MASTER_CLASS = 'create_master_class';
    case UPDATE_MASTER_CLASS = 'update_master_class';
    case DELETE_MASTER_CLASS = 'delete_master_class';

    // Chapter Permissions
    case VIEW_CHAPTER = 'view_chapter';
    case CREATE_CHAPTER = 'create_chapter';
    case UPDATE_CHAPTER = 'update_chapter';
    case DELETE_CHAPTER = 'delete_chapter';

    // Subscription Permissions
    case VIEW_SUBSCRIPTION = 'view_subscription';
    case CREATE_SUBSCRIPTION = 'create_subscription';
    case UPDATE_SUBSCRIPTION = 'update_subscription';
    case DELETE_SUBSCRIPTION = 'delete_subscription';

    // Exam Permissions
    case VIEW_EXAM = 'view_exam';
    case CREATE_EXAM = 'create_exam';
    case UPDATE_EXAM = 'update_exam';
    case DELETE_EXAM = 'delete_exam';
    case SUBMIT_EXAM = 'submit_exam';

    // Progress Permissions
    case VIEW_PROGRESS = 'view_progress';
    case UPDATE_PROGRESS = 'update_progress';
}
