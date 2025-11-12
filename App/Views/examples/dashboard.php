<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Example - Phuse Template System</title>
  <link rel="stylesheet" href="{assetsUrl}css/styles.css">
</head>

<body>
  <div class="container py-2">
    <div class="card shadow mx-auto max-width-lg">
      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Dashboard Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="card-body p-4">
        <div class="card border-danger p-3 mb-4">
          <h6 class="mb-3">🎯 Advanced Features Demonstrated:</h6>
          <ul class="mb-0 text-secondary">
            <li><code class="highlight">&lbrace;stats.total_users&rbrace;</code> - Nested object access</li>
            <li><code class="highlight">&lbrace;% foreach recent_activity as activity %&rbrace;</code> - Complex data iteration</li>
            <li><code class="highlight">&lbrace;notification.type&rbrace;</code> - Conditional styling with dynamic classes</li>
            <li><code class="highlight">&lbrace;user.role&rbrace;</code> - Role-based content display</li>
          </ul>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
          <div class="col">
            <div class="card text-center p-4 border-primary">
              <div class="text-primary small fw-bold mb-2">System Statistics</div>
              <div class="h2 text-primary mb-1">{stats.total_users}</div>
              <div class="text-muted small">Total Users</div>
            </div>
          </div>

          <div class="col">
            <div class="card text-center p-4 border-primary">
              <div class="text-primary small fw-bold mb-2">Active Sessions</div>
              <div class="h2 text-primary mb-1">{stats.active_sessions}</div>
              <div class="text-muted small">Currently Online</div>
            </div>
          </div>

          <div class="col">
            <div class="card text-center p-4 border-primary">
              <div class="text-primary small fw-bold mb-2">Pending Orders</div>
              <div class="h2 text-primary mb-1">{stats.pending_orders}</div>
              <div class="text-muted small">Awaiting Processing</div>
            </div>
          </div>

          <div class="col">
            <div class="card text-center p-4 border-primary">
              <div class="text-primary small fw-bold mb-2">User Profile</div>
              <div class="h2 text-primary mb-1">{user.name}</div>
              <div class="text-muted small">{user.role|title} Account</div>
            </div>
          </div>
        </div>

        <div class="card border-info p-4 mb-4">
          <h6 class="text-primary mb-3">📋 Recent Activity</h6>
          {% foreach recent_activity as activity %}
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
            <span class="text-primary fw-bold">{activity.action}</span>
            <span class="text-info small">{activity.time}</span>
          </div>
          {% endforeach %}
        </div>

        <div class="card border-success p-4 mb-4">
          <h6 class="text-success mb-3">🔔 System Notifications</h6>
          {% foreach notifications as notification %}
          <div class="d-flex align-items-center p-2 mb-2 rounded {% if notification.type == 'warning' %}bg-warning{% elseif notification.type == 'error' %}bg-danger{% else %}bg-info{% endif %}">
            <span class="me-3">
              {% if notification.type == 'warning' %}⚠️{% elseif notification.type == 'error' %}❌{% else %}ℹ️{% endif %}
            </span>
            <span class="text-secondary">{notification.message}</span>
          </div>
          {% endforeach %}
        </div>

        <div class="alert alert-info mt-4">
          <strong>Dynamic Dashboard:</strong> This template demonstrates a complete admin dashboard with statistics, user information, activity feeds, and dynamic notifications with conditional styling.
        </div>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {year}</p>
      </div>
    </div>
  </div>


</body>
</html>
