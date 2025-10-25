/* Modal de Logout */
#logoutModal .modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

#logoutModal .modal-header {
    padding: 1.5rem 1.5rem 0;
}

#logoutModal .modal-title {
    font-weight: 600;
    color: #214247;
}

#logoutModal .modal-body {
    padding: 2rem 1.5rem;
}

#logoutModal .modal-footer {
    padding: 0 1.5rem 1.5rem;
}

#logoutModal .btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

#logoutModal .btn-secondary {
    background-color: #6c757d;
    border: none;
}

#logoutModal .btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
}

#logoutModal .btn-danger {
    background-color: #dc3545;
    border: none;
}

#logoutModal .btn-danger:hover {
    background-color: #c82333;
    transform: translateY(-2px);
}

#logoutModal .bi-question-circle {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}