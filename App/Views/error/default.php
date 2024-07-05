<head>
  <meta charset="utf-8">
  <title>Oops! Something is not right</title>
  <style>
	body {
		background-color: #131313;
		margin: 2rem;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #f4f4f4;
	}
	.error-template {padding: 4rem 1rem;text-align: center;}
	.error-actions {margin-top:1rem;margin-bottom:2rem;}
	.error-actions .btn { margin-right:1rem; }
	.btn{ 
		border: 1px solid #7d94b6;
		background-color: #131313;
		display: inline-block;
		padding: 0.5rem 1.5rem;
		border-radius: 5%;
		margin: 0.5rem;
		color: #f4f4f4;
		text-decoration:none;
	}
	</style>
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="error-template">
					<h1> Oops!</h1>
					<h2> Something is not right.</h2>
					<div class="error-details">
						<p>
							Sorry, an error has occured:<br/>
							{message}
						</p>
						<a href="#" onclick="history.back()" type="button" class="btn">Back</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>