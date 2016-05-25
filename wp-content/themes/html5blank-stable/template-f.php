<?php /* Template Name: Template F */ get_header();?>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<main role="main">
		<!-- section -->
		<section class="container-fluid">
			<div class="row">
				<div class="col-sm-5 side">
					<div class="in-side row">
						<div class="col-xs-6 col-sm-12">
							<h1><?php the_title(); ?></h1>
							<div class="blurb">
								<?php echo types_render_field("contact-blurb", array()); ?>
							</div>
						</div>

						<div class="col-xs-6 col-sm-12">
							<h3>Contact Information</h3>
							<?php echo types_render_field("contact-numbers", array("separator"=>"<br>")); ?>
							<br>
							<p>
							Email: <?php echo types_render_field("contact-email", array()); ?>
							</p>

							<h3>Address</h3>
							<?php echo types_render_field("contact-address", array()); ?>
						</div>
					</div>
				</div>
				<div class="col-sm-7">
					<div class="form-container">
						<?php the_content();?>
					</div>
				</div>
			</div>


		</section>
		<!-- /section -->
	</main>

<?php endwhile; ?>
<?php endif; ?>
<?php get_footer(); ?>
