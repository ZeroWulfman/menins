<?php /* Template Name: Two Template */ get_header();
include("quote.php");
?>

	<main role="main">
		<!-- section -->
		<section>

			<h1><?php the_title(); ?></h1>

			<?php the_content() ?>

		</section>
		<!-- /section -->
	</main>


<?php get_footer(); ?>
