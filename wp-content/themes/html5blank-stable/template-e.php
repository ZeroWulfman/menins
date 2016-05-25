<?php /* Template Name:Template E*/ get_header();
$args = array(
	'post_type' => 'staff-member',
	'post_status' => 'publish',
	'posts_per_page' => -1,
	'orderby' => 'date',
	'order' => 'ASC'
);
$my_query = new WP_Query($args);
?>
	<main role="main">
		<!-- section -->
		<section class="container-fluid">

			<h1><?php the_title(); ?></h1>
			<div class="banner-photo">

			</div>

			<div id="staff-accordion">
				<div class="container-fluid">
					<div class="row">
						<div class="">
						<div class="wrap">
							<div class="panel" id="accordion" role="tablist" aria-multiselectable="true">


						<?php $acc=0; if ( $my_query->have_posts() ) : while ( $my_query->have_posts() ) : $my_query->the_post(); $acc++; ?>

						<div class="panel panel-default">
							<div class="panel-heading outer-tab" role="tab" id="heading-<?php echo $acc;?>">
								<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?php echo $acc;?>" aria-expanded="true" aria-controls="collapse-<?php echo $acc;?>">
									<h2 class="panel-title">
										<?php the_title(); ?>
									</h2>
								</a>
								<i class="chevron indicator glyphicon glyphicon-chevron-up  pull-right"></i>
							</div>

							<div id="collapse-<?php echo $acc;?>" class="panel-collapse collapse inner-tab" role="tab-panel" aria-labelledby="heading-<?php echo $acc;?>">
								<div class="panel-body">
									<!-- post thumbnail -->
									<?php if ( has_post_thumbnail()) : // Check if Thumbnail exists ?>
											<?php the_post_thumbnail('medium'); // Fullsize image for the single post ?>
									<!-- /post thumbnail -->
									<?php endif; ?>
									<p class=""><?php echo types_render_field("staff-name",array()); ?><br>
									<?php echo types_render_field("staff-email",array()); ?>
									</p>
									<?php the_content();?>
								</div>
							</div>
						</div>
						<?php endwhile;?>
							</div>
						</div>
						 <?php endif;?>
						</div>
					</div>
				</div>
			</div>

		</section>
		<!-- /section -->
	</main>

<?php get_footer(); ?>
