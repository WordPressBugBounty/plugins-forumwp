<?php
/**
 * Template for the single forum info
 *
 * This template can be overridden by copying it to your-theme/forumwp/single-forum-info.php
 *
 * @version 2.1.3
 *
 * @var array $fmwp_single_forum_info
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$forum_id = $fmwp_single_forum_info['id'];
if ( FMWP()->is_forum_page() ) {
	$forum = $post;
} else {
	$forum = get_post( $forum_id );
}

$show_header    = ! empty( $fmwp_single_forum_info['show_header'] ) ? $fmwp_single_forum_info['show_header'] : false;
$unlogged_class = FMWP()->frontend()->shortcodes()->unlogged_class();

$forum_categories = array();
if ( FMWP()->options()->get( 'forum_categories' ) ) {
	$forum_categories = FMWP()->common()->forum()->get_categories( $forum_id );
}

$show_title_row = true;
if ( ! count( $forum_categories ) && wp_is_block_theme() && FMWP()->is_forum_page() ) {
	$show_title_row = false;
}
?>

<span class="fmwp-forum-info">
	<?php
	$featured = get_the_post_thumbnail( $forum_id, 'thumbnail' );

	$no_avatar_class = '';
	$icon            = false;
	$icon_color      = false;
	$icon_bgcolor    = false;
	if ( empty( $featured ) ) {
		$icon = get_post_meta( $forum_id, 'fmwp_icon', true );
		if ( ! empty( $icon ) ) {
			$icon_bgcolor = get_post_meta( $forum_id, 'fmwp_icon_bgcolor', true );
			$icon_bgcolor = empty( $icon_bgcolor ) ? '#000' : $icon_bgcolor;

			$icon_color = get_post_meta( $forum_id, 'fmwp_icon_color', true );
			$icon_color = empty( $icon_color ) ? '#fff' : $icon_color;
		}
	}

	if ( ! empty( $featured ) ) {
		?>
		<span class="fmwp-forum-avatar">
			<?php echo wp_kses( $featured, FMWP()->get_allowed_html( 'templates' ) ); ?>
		</span>
		<?php
	} elseif ( ! empty( $icon ) ) {
		?>
		<span class="fmwp-forum-avatar fmwp-forum-icon" style="color: <?php echo esc_attr( $icon_color ); ?>; background-color: <?php echo esc_attr( $icon_bgcolor ); ?>;">
			<i class="<?php echo esc_attr( $icon ); ?>"></i>
		</span>
		<?php
	} else {
		$no_avatar_class = ' fmwp-forum-no-avatar';
	}
	?>

	<span class="fmwp-forum-data<?php echo ! empty( $no_avatar_class ) ? esc_attr( $no_avatar_class ) : ''; ?>">
		<?php if ( $show_title_row ) { ?>
			<span class="fmwp-forum-data-line">
				<?php
				if ( ! wp_is_block_theme() ) {
					if ( ! $show_header && FMWP()->is_forum_page() ) {
						the_title( '<h1 class="entry-title">', '</h1>' );
					} elseif ( $show_header && FMWP()->is_forum_page() ) {
						the_title( '<h1 class="entry-title">', '</h1>' );
					} else {
						?>
						<h3>
							<?php if ( FMWP()->common()->forum()->is_locked( $forum_id ) ) { ?>
								<span class="fmwp-tip-n" title="<?php esc_attr_e( 'Locked', 'forumwp' ); ?>">
									<i class="fas fa-lock"></i>
								</span>
								<?php
							}

							echo esc_html( $forum->post_title );
							?>
						</h3>
						<?php
					}
				} else {
					?>
					<h3>
						<?php
						if ( FMWP()->common()->forum()->is_locked( $forum_id ) ) {
							?>
							<span class="fmwp-tip-n" title="<?php esc_attr_e( 'Locked', 'forumwp' ); ?>">
								<i class="fas fa-lock"></i>
							</span>
							<?php
						}

						if ( ! FMWP()->is_forum_page() ) {
							echo esc_html( get_the_title( $forum_id ) );
						}
						?>
					</h3>
					<?php
				}

				if ( FMWP()->options()->get( 'forum_categories' ) ) {
					$forum_categories = FMWP()->common()->forum()->get_categories( $forum_id );

					if ( count( $forum_categories ) ) {
						?>
						<span class="fmwp-forum-categories">
							<?php foreach ( $forum_categories as $category ) { ?>
								<span class="fmwp-forum-category">
									<a href="<?php echo esc_url( get_term_link( $category->term_id, 'fmwp_forum_category' ) ); ?>">
										<?php echo esc_html( $category->name ); ?>
									</a>
								</span>
							<?php } ?>
						</span>
						<?php
					}
				}
				?>
			</span>
		<?php } ?>
		<?php remove_filter( 'the_content', array( FMWP()->frontend()->shortcodes(), 'cpt_content' ) ); ?>
		<span class="fmwp-forum-data-line fmwp-forum-content">
			<?php
			if ( FMWP()->is_forum_page() ) {
				the_content();
			} else {
				echo wp_kses( $forum->post_content, FMWP()->get_allowed_html( 'templates' ) );
			}
			?>
		</span>
		<?php add_filter( 'the_content', array( FMWP()->frontend()->shortcodes(), 'cpt_content' ) ); ?>
	</span>
</span>
