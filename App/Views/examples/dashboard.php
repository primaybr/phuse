<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Example - Phuse Template System</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: rgb(25, 25, 25);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .example-container {
      max-width: 1200px;
      margin: 2rem auto;
      background: rgb(75, 75, 75);
      border-radius: 8px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    .example-header {
      background: rgb(40, 40, 40);
      color: white;
      padding: 2rem 1.5rem;
      margin-bottom: 1rem;
    }
    .example-content {
      padding: 0 2rem 2rem;
      line-height: 1.7;
      color: rgb(205, 205, 205);
    }
    .example-footer {
      text-align: center;
      padding: 1rem;
      margin-top: auto;
      color: rgb(205, 205, 205);
      font-size: 0.9rem;
      border-top: 1px solid rgb(177, 177, 177);
    }
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    .stat-card {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      border-left: 4px solid #0d6efd;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }
    .stat-value {
      color: #ffffff;
      font-size: 2.5rem;
      font-weight: 700;
      margin: 1rem 0 0.5rem;
    }
    .stat-label {
      color: #0d6efd;
      font-size: 1.1rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .stat-description {
      color: #6c757d;
      font-size: 0.9rem;
      margin-top: 0.5rem;
    }
    .activity-section {
      background: rgba(111, 66, 193, 0.1);
      border: 1px solid rgba(111, 66, 193, 0.3);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1.5rem 0;
    }
    .activity-item {
      background: rgb(30, 30, 30);
      border-radius: 8px;
      padding: 1rem 1.5rem;
      margin: 0.75rem 0;
      border-left: 4px solid #6f42c1;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .activity-action {
      color: #ffffff;
      font-weight: 500;
    }
    .activity-time {
      color: #b197fc;
      font-size: 0.9rem;
      font-style: italic;
    }
    .notifications-section {
      background: rgba(25, 135, 84, 0.1);
      border: 1px solid rgba(25, 135, 84, 0.3);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1.5rem 0;
    }
    .notification {
      background: rgb(30, 30, 30);
      border-radius: 8px;
      padding: 1rem 1.5rem;
      margin: 0.75rem 0;
      border-left: 4px solid #198754;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .notification.warning {
      border-left-color: #fd7e14;
    }
    .notification.info {
      border-left-color: #0dcaf0;
    }
    .notification.error {
      border-left-color: #dc3545;
    }
    .notification-icon {
      font-size: 1.2rem;
    }
    .notification-message {
      color: rgb(205, 205, 205);
      margin: 0;
    }
    .highlight {
      background: rgba(13, 110, 253, 0.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      color:rgb(0, 128, 255);
    }
    .features-demo {
      background: rgba(220, 53, 69, 0.1);
      border-left: 3px solid #dc3545;
      padding: 1.5rem;
      margin: 1.5rem 0;
      border-radius: 6px;
    }
  </style>
</head>

<body>
  <div class="container py-2">
    <div class="example-container">
      <div class="example-header">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Dashboard Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <div class="features-demo mb-4">
          <h6 class="mb-3">🎯 Advanced Features Demonstrated:</h6>
          <ul class="mb-0" style="color: rgb(205, 205, 205);">
            <li><code class="highlight">{stats.total_users}</code> - Nested object access</li>
            <li><code class="highlight">{% foreach recent_activity as activity %}</code> - Complex data iteration</li>
            <li><code class="highlight">{notification.type}</code> - Conditional styling with dynamic classes</li>
            <li><code class="highlight">{user.role}</code> - Role-based content display</li>
          </ul>
        </div>

        <div class="dashboard-grid">
          <div class="stat-card">
            <div class="stat-label">System Statistics</div>
            <div class="stat-value">{stats.total_users}</div>
            <div class="stat-description">Total Users</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">Active Sessions</div>
            <div class="stat-value">{stats.active_sessions}</div>
            <div class="stat-description">Currently Online</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">Pending Orders</div>
            <div class="stat-value">{stats.pending_orders}</div>
            <div class="stat-description">Awaiting Processing</div>
          </div>

          <div class="stats-badge">
            {examples|length} Examples
          </div>

          <div class="stat-card">
            <div class="stat-label">User Profile</div>
            <div class="stat-value">{user.name}</div>
            <div class="stat-description">{user.role|title} Account</div>
          </div>
        </div>

        <div class="activity-section">
          <h6 class="text-primary mb-3">📋 Recent Activity</h6>
          {% foreach recent_activity as activity %}
          <div class="activity-item">
            <span class="activity-action">{activity.action}</span>
            <span class="activity-time">{activity.time}</span>
          </div>
          {% endforeach %}
        </div>

        <div class="notifications-section">
          <h6 class="text-success mb-3">🔔 System Notifications</h6>
          {% foreach notifications as notification %}
          <div class="notification {notification.type}">
            <span class="notification-icon">
              {% if notification.type == 'warning' %}⚠️{% endif %}
              {% if notification.type == 'info' %}ℹ️{% endif %}
              {% if notification.type == 'error' %}❌{% endif %}
            </span>
            <span class="notification-message">{notification.message}</span>
          </div>
          {% endforeach %}
        </div>

        <div class="alert alert-info mt-4">
          <strong>Dynamic Dashboard:</strong> This template demonstrates a complete admin dashboard with statistics, user information, activity feeds, and dynamic notifications with conditional styling.
        </div>
      </div>

      <div class="example-footer">
        <p class="mb-0">Phuse Framework Template System &copy; 2025</p>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
