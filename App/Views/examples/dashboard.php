<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Example - Phuse Template System</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css?v=141">
  <link rel="icon" type="image/svg+xml" href="{{assetsUrl}}images/favicon.svg">
  <script>(function(){try{var t=localStorage.getItem('phuse-theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}})()</script>
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
          <h6 class="mb-3"><i class="pi pi-zap me-1"></i> Advanced Features Demonstrated:</h6>
          <ul class="mb-0 text-secondary">
            <li><code>{{stats.total_users}}</code> - Nested object access</li>
            <li><code>{% foreach recent_activity as activity %}</code> - Complex data iteration</li>
            <li><code>{{notification.type}}</code> - Conditional styling with dynamic classes</li>
            <li><code>{{user.role}}</code> - Role-based content display</li>
          </ul>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
          <div class="col">
            <div class="card text-center p-4 border-primary">
              <div class="text-primary small fw-bold mb-2">System Statistics</div>
              <div class="h2 text-primary mb-1">{{stats.total_users}}</div>
              <div class="text-muted small">Total Users</div>
            </div>
          </div>

          <div class="col">
            <div class="card text-center p-4 border-primary">
              <div class="text-primary small fw-bold mb-2">Active Sessions</div>
              <div class="h2 text-primary mb-1">{{stats.active_sessions}}</div>
              <div class="text-muted small">Currently Online</div>
            </div>
          </div>

          <div class="col">
            <div class="card text-center p-4 border-primary">
              <div class="text-primary small fw-bold mb-2">Pending Orders</div>
              <div class="h2 text-primary mb-1">{{stats.pending_orders}}</div>
              <div class="text-muted small">Awaiting Processing</div>
            </div>
          </div>

          <div class="col">
            <div class="card text-center p-4 border-primary">
              <div class="text-primary small fw-bold mb-2">User Profile</div>
              <div class="h2 text-primary mb-1">{{user.name}}</div>
              <div class="text-muted small">{{user.role|title}} Account</div>
            </div>
          </div>
        </div>

        <div class="card border-info p-4 mb-4">
          <h6 class="text-primary mb-3"><i class="pi pi-list me-1"></i> Recent Activity</h6>
          {% foreach recent_activity as activity %}
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
            <span class="text-primary fw-bold">{{activity.action}}</span>
            <span class="text-info small">{{activity.time}}</span>
          </div>
          {% endforeach %}
        </div>

        <div class="card border-success p-4 mb-4">
          <h6 class="text-success mb-3"><i class="pi pi-bell me-1"></i> System Notifications</h6>
          {% foreach notifications as notification %}
          <div class="d-flex align-items-center p-2 mb-2 rounded {% if notification.type == 'warning' %}bg-warning{% elseif notification.type == 'error' %}bg-danger{% else %}bg-info{% endif %}">
            <span class="me-3">
              {% if notification.type == 'warning' %}<i class="pi pi-alert-triangle"></i>{% elseif notification.type == 'error' %}<i class="pi pi-x-circle"></i>{% else %}<i class="pi pi-info"></i>{% endif %}
            </span>
            <span class="text-secondary">{{notification.message}}</span>
          </div>
          {% endforeach %}
        </div>

        <div class="alert alert-info mt-4">
          <strong>Dynamic Dashboard:</strong> This template demonstrates a complete admin dashboard with statistics, user information, activity feeds, and dynamic notifications with conditional styling.
        </div>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {{year}}</p>
      </div>
    </div>
  </div>

  <script src="{{assetsUrl}}js/scripts.js?v=136"></script>
</body>
</html>
