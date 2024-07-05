<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to Phuse!</title>
  <style>
	body {
		background-color: #131313;
		margin: 2rem;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #f4f4f4;
	}

	h1 {
		background-color: transparent;
		border-bottom: 1px solid #5575a2;
		font-weight: normal;
		padding: 15px 15px 10px 15px;
		margin:0;
	}

	.clearfix:after {
		content: ".";
		display: block;
		clear: both;
		visibility: hidden;
		line-height: 0;
		height: 0;
	}
	.clearfix {
		display: inline-block;
	}

	#wrapper{
		margin: 1rem;
		border: 1px solid #5575a2;
		box-shadow: 0.15rem 0.15rem 0.1rem 0.1rem #1f1f1f;
	}

	.content{
		margin-left: 15px;
	}

	footer {
		text-align: center;
		font-size: 12px;
		border-top: 1px solid #5575a2;
		line-height: 32px;
		padding: 0 1rem 0 1rem;
		margin: 2rem 0 0 0;
	}
  </style>
</head>

<body>
    <div id="wrapper">
        <header>
            <h1>Welcome to Phuse!</h1>
        </header>
        <main class="content clearfix">
            <p>
                Phuse is a PHP framework that simplifies web development with conventions and helpers. It follows the convention over configuration principle, which means that it has sensible defaults for most settings and features, reducing the need for manual configuration. It also provides a variety of helpers, which are functions that perform common tasks such as formatting, validation, pagination, and more. Phuse aims to make web development more enjoyable and productive with PHP.
            </p>
        </main>
        <footer>
            <p>Phuse © {date}</p>
        </footer>
    </div>

</body>
</html>