<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phuse Framework - Bootstrap-Compatible Components</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        body { background-color: var(--bg-primary); color: var(--text-primary); padding: 2rem 0; }
        .demo-section { margin-bottom: 3rem; padding: 1.5rem; border-radius: var(--border-radius); background-color: var(--bg-secondary); }
        .demo-title { color: var(--primary); margin-bottom: 1rem; font-size: 1.5rem; }
        pre { background-color: var(--bg-tertiary); padding: 1rem; border-radius: var(--border-radius); overflow-x: auto; }
        code { color: #e0e0e0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Phuse Framework - Bootstrap Compatible Components</h1>
        <p>Enhanced with missing Bootstrap 5.3.8 components: Alert, Button, Carousel, Offcanvas, Popover, ScrollSpy, Tooltip</p>

        <div class="demo-section">
            <h2 class="demo-title">Alert Components</h2>
            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                <strong>Primary Alert:</strong> This is a primary alert with a dismissible close button.
                <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success Alert:</strong> Operation completed successfully.
                <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error Alert:</strong> Something went wrong. Please try again.
                <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
            </div>
            <pre><code><div class="alert alert-primary alert-dismissible fade show">
  <strong>Primary Alert:</strong> This is a primary alert...
  <button type="button" class="btn-close" data-dismiss="alert"></button>
</div></code></pre>
        </div>

        <div class="demo-section">
            <h2 class="demo-title">Button States & Toggle</h2>
            <button type="button" class="btn btn-primary" data-toggle="button">Primary Button</button>
            <button type="button" class="btn btn-secondary" data-toggle="button">Secondary Button</button>
            <button type="button" class="btn btn-success" data-toggle="button">Success Button</button>
            <pre><code><button class="btn btn-primary" data-toggle="button">Primary Button</button></code></pre>
        </div>

        <div class="demo-section">
            <h2 class="demo-title">Carousel Component</h2>
            <div id="demo-carousel" class="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div style="height: 200px; background: linear-gradient(45deg, var(--primary), var(--primary-dark)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">Slide 1</div>
                    </div>
                    <div class="carousel-item">
                        <div style="height: 200px; background: linear-gradient(45deg, var(--success), var(--success-dark)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">Slide 2</div>
                    </div>
                    <div class="carousel-item">
                        <div style="height: 200px; background: linear-gradient(45deg, var(--info), var(--primary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">Slide 3</div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
                <div class="carousel-indicators">
                    <button type="button" data-slide-to="0" class="active"></button>
                    <button type="button" data-slide-to="1"></button>
                    <button type="button" data-slide-to="2"></button>
                </div>
            </div>
            <pre><code><div class="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">...</div>
  </div>
  <button data-slide="prev"></button>
  <button data-slide="next"></button>
</div></code></pre>
        </div>

        <div class="demo-section">
            <h2 class="demo-title">Offcanvas Component</h2>
            <button class="btn btn-primary" type="button" data-toggle="offcanvas" data-target="#demo-offcanvas">Open Offcanvas</button>

            <div class="offcanvas offcanvas-end" tabindex="-1" id="demo-offcanvas">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Offcanvas Menu</h5>
                    <button type="button" class="btn-close" data-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <p>This is an offcanvas sidebar that slides in from the right. You can place navigation, forms, or any content here.</p>
                    <div class="mt-3">
                        <button class="btn btn-outline-primary">Button 1</button>
                        <button class="btn btn-outline-secondary">Button 2</button>
                    </div>
                </div>
            </div>
            <pre><code><button data-toggle="offcanvas" data-target="#my-offcanvas">Open</button>
<div class="offcanvas offcanvas-end" id="my-offcanvas">
  <div class="offcanvas-header"></div>
  <div class="offcanvas-body">...</div>
</div></code></pre>
        </div>

        <div class="demo-section">
            <h2 class="demo-title">Popover Component</h2>
            <button class="btn btn-primary" type="button" data-toggle="popover" title="Popover Title" data-content="This is a popover with some content inside. You can include any text or HTML here.">
                Click for Popover
            </button>
            <button class="btn btn-secondary ms-2" type="button" data-toggle="popover" title="Another Popover" data-content="Popovers are great for showing additional information without cluttering the UI.">
                Another Popover
            </button>
            <pre><code><button data-toggle="popover" title="Title" data-content="Content">Click</button></code></pre>
        </div>

        <div class="demo-section">
            <h2 class="demo-title">Tooltip Component</h2>
            <div class="d-flex gap-3 align-items-center">
                <button class="btn btn-primary" type="button" data-toggle="tooltip" title="This is a primary tooltip">Primary Tooltip</button>
                <button class="btn btn-success" type="button" data-toggle="tooltip" title="This is a success tooltip">Success Tooltip</button>
                <button class="btn btn-danger" type="button" data-toggle="tooltip" title="This is a danger tooltip">Danger Tooltip</button>
                <button class="btn btn-info" type="button" data-toggle="tooltip" data-placement="top" title="Top positioned tooltip">Top Tooltip</button>
            </div>
            <pre><code><button data-toggle="tooltip" title="Tooltip text">Hover me</button></code></pre>
        </div>

        <div class="demo-section">
            <h2 class="demo-title">ScrollSpy Component</h2>
            <div class="row">
                <div class="col-4">
                    <nav id="navbar-example3" class="navbar navbar-light bg-light flex-column align-items-stretch p-3">
                        <nav class="nav nav-pills flex-column">
                            <a class="nav-link" href="#item-1">Item 1</a>
                            <a class="nav-link" href="#item-2">Item 2</a>
                            <a class="nav-link" href="#item-3">Item 3</a>
                            <a class="nav-link" href="#item-4">Item 4</a>
                        </nav>
                    </nav>
                </div>
                <div class="col-8">
                    <div data-spy="scroll" data-target="#navbar-example3" data-offset="0" class="scrollspy-example" tabindex="0" style="height: 200px; overflow-y: scroll;">
                        <h4 id="item-1">Item 1</h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>

                        <h4 id="item-2">Item 2</h4>
                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

                        <h4 id="item-3">Item 3</h4>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>

                        <h4 id="item-4">Item 4</h4>
                        <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet.</p>
                    </div>
                </div>
            </div>
            <pre><code><nav data-spy="scroll" data-target="#nav">
  <a href="#section1">Section 1</a>
  <a href="#section2">Section 2</a>
</nav></code></pre>
        </div>

        <div class="demo-section">
            <h2 class="demo-title">Toast Notifications</h2>
            <button class="btn btn-primary" onclick="showToast('success')">Show Success Toast</button>
            <button class="btn btn-danger" onclick="showToast('error')">Show Error Toast</button>
            <button class="btn btn-info" onclick="showToast('info')">Show Info Toast</button>
            <script>
                function showToast(type) {
                    const messages = {
                        success: 'Operation completed successfully!',
                        error: 'An error occurred. Please try again.',
                        info: 'This is an informational message.'
                    };
                    Phuse.toast(messages[type], type);
                }
            </script>
            <pre><code>Phuse.toast('Message', 'success'); // or 'error', 'info', 'warning'</code></pre>
        </div>

        <div class="demo-section">
            <h2 class="demo-title">Enhanced Accordion</h2>
            <div class="accordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button">Accordion Item #1</button>
                    </h2>
                    <div class="accordion-body">
                        <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions.
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button">Accordion Item #2</button>
                    </h2>
                    <div class="accordion-body" style="max-height: 0;">
                        <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions.
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button">Accordion Item #3</button>
                    </h2>
                    <div class="accordion-body" style="max-height: 0;">
                        <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/scripts.js"></script>
</body>
</html>
