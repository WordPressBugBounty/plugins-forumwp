<?php
/**
 * Template for the topic
 *
 * This template can be overridden by copying it to your-theme/forumwp/topic.php
 *
 * @version 2.1.3
 *
 * @var array $fmwp_topic
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topic = get_post( $fmwp_topic['id'] );

if ( post_password_required( $topic ) ) {

	echo wp_kses( get_the_password_form( $topic ), FMWP()->get_allowed_html( 'templates' ) );

} else {

	$forum_id = FMWP()->common()->topic()->get_forum_id( $fmwp_topic['id'] );
	$forum    = get_post( $forum_id );

	$show_header = isset( $fmwp_topic['show_header'] ) && 'yes' === $fmwp_topic['show_header'];

	FMWP()->get_template_part(
		'js/replies-list',
		array(
			'actions'  => 'edit',
			'topic_id' => $fmwp_topic['id'],
		)
	);

	FMWP()->get_template_part(
		'js/single-reply-subreplies',
		array(
			'item'   => 'data',
			'active' => true,
		)
	);

	if ( FMWP()->options()->get( 'topic_tags' ) ) {
		FMWP()->get_template_part( 'js/single-topic-tags' );
	}

	$unlogged_class = FMWP()->frontend()->shortcodes()->unlogged_class();

	$visibility = get_post_meta( $forum_id, 'fmwp_visibility', true );

	$forum_link = get_permalink( $forum_id );

	$author      = get_userdata( $topic->post_author );
	$author_link = FMWP()->user()->get_profile_link( $topic->post_author );

	setup_postdata( $fmwp_topic['id'] );

	//Topic dropdown actions
	$topic_dropdown_items = array();

	$replies_count = FMWP()->common()->topic()->get_statistics( $fmwp_topic['id'], 'replies' );

	$status_classes = '';
	if ( 'public' === $visibility || is_user_logged_in() ) {
		if ( FMWP()->common()->topic()->is_spam( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-spam';
		}

		if ( FMWP()->common()->topic()->is_trashed( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-trashed';
		}

		if ( FMWP()->common()->topic()->is_locked( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-locked';
		}

		if ( FMWP()->common()->topic()->is_pending( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-pending';
		}

		if ( FMWP()->common()->topic()->is_pinned( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-pinned';
		}

		if ( FMWP()->common()->topic()->is_announcement( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-announcement';
		}

		if ( FMWP()->common()->topic()->is_global( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-global';
		}

		if ( is_user_logged_in() ) {
			if ( FMWP()->reports()->is_reported_by_user( $fmwp_topic['id'], get_current_user_id() ) ) {
				$status_classes .= ' fmwp-topic-reported';
			} elseif ( current_user_can( 'fmwp_see_reports' ) && FMWP()->reports()->is_reported( $fmwp_topic['id'] ) ) {
				$status_classes .= ' fmwp-topic-reported';
			}
		}

		$status_classes = apply_filters( 'fmwp_topic_status_classes', $status_classes, $fmwp_topic );

		$actions_list = FMWP()->common()->topic()->actions_list( $topic );
		foreach ( $actions_list as $key => $data ) {
			$topic_dropdown_items[] = '<a href="#" data-entity_id="' . esc_attr( $fmwp_topic['id'] ) . '" class="' . esc_attr( $key ) . '" data-nonce="' . esc_attr( $data['nonce'] ) . '">' . esc_html( $data['title'] ) . '</a>';
		}
	}

	do_action( 'fmwp_before_individual_topic' );
	$wrapper_classes = array(
		'fmwp',
		'fmwp-topic-main-wrapper',
	);
	if ( ! empty( $unlogged_class ) ) {
		$wrapper_classes[] = 'fmwp-unlogged-data';
	}
	?>

	<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) . $status_classes ); ?>">
		<?php
		if ( 'public' !== $visibility && ! is_user_logged_in() ) {
			// translators: %s is the class attribute
			echo wp_kses( sprintf( __( 'Please <a href="#" class="%s" title="Login to view" data-fmwp_popup_title="Login to view topic">login</a> to view this topic', 'forumwp' ), $unlogged_class ), FMWP()->get_allowed_html( 'templates' ) );
		} else {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! empty( $_GET['fmwp-msg'] ) ) {
				do_action( 'fmwp_topic_header_message', $topic, sanitize_key( $_GET['fmwp-msg'] ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification
			?>

			<?php if ( 'no' !== sanitize_key( $fmwp_topic['show_header'] ) ) { ?>
				<div class="fmwp-topic-head">
					<?php
					if ( $show_header || ! FMWP()->is_topic_page() ) {
						if ( ! wp_is_block_theme() || ! FMWP()->is_topic_page() ) {
							if ( $show_header && FMWP()->is_topic_page() ) {
								?>
								<h1><?php echo esc_html( $topic->post_title ); ?></h1>
								<?php
							} else {
								?>
								<h3><?php echo esc_html( $topic->post_title ); ?></h3>
								<?php
							}
						}
					}
					?>
					<span class="fmwp-topic-info">
						<?php if ( ! empty( $forum ) && ! empty( FMWP()->options()->get( 'show_forum' ) ) && 'no' !== sanitize_key( $fmwp_topic['show_forum'] ) ) { ?>
							<a href="<?php echo esc_url( $forum_link ); ?>" title="<?php echo esc_attr( $forum->post_title ); ?>" class="fmwp-topic-forum-link">
								<?php echo esc_html( $forum->post_title ); ?>
							</a>
						<?php } ?>
						<span class="fmwp-topic-stats">
							<?php
							$replies = FMWP()->common()->topic()->get_statistics( $fmwp_topic['id'], 'replies' );
							// translators: %s is a total replies count
							echo wp_kses_post( sprintf( _n( '<span id="fmwp-replies-total">%s</span> reply', '<span id="fmwp-replies-total">%s</span> replies', $replies, 'forumwp' ), $replies ) );
							?>
						</span>

						<?php do_action( 'fmwp_topic_stats', $fmwp_topic ); ?>

						<span class="fmwp-topic-stats">
							<?php
							$views = FMWP()->common()->topic()->get_statistics( $fmwp_topic['id'], 'views' );
							// translators: %s is a total topics count
							echo wp_kses_post( sprintf( _n( '<span id="fmwp-views-total">%s</span> view', '<span id="fmwp-views-total">%s</span> views', $views, 'forumwp' ), $views ) );
							?>
						</span>
						<?php if ( FMWP()->options()->get( 'topic_tags' ) ) { ?>
							<?php
							$topic_tags = FMWP()->common()->topic()->get_tags( $topic->ID );

							if ( count( $topic_tags ) ) {
								?>
								<span class="fmwp-topic-stats fmwp-tags-stats">
									<?php esc_html_e( 'Tags:', 'forumwp' ); ?>&nbsp;
									<span class="fmwp-topic-tags-list">
										<?php
										$tag_links = array();
										foreach ( $topic_tags as $topic_tag ) {
											$tag_links[] = '<a href="' . esc_url( get_term_link( $topic_tag->term_id, 'fmwp_topic_tag' ) ) . '">' . esc_html( $topic_tag->name ) . '</a>';
										}
										echo wp_kses( implode( ', ', $tag_links ), FMWP()->get_allowed_html( 'templates' ) );
										?>
									</span>
								</span>
								<?php
							}
						}
						?>
					</span>
				</div>
			<?php } ?>

			<div class="fmwp-topic-content">
				<div class="fmwp-topic-base" data-topic_id="<?php echo esc_attr( $fmwp_topic['id'] ); ?>"
					data-trashed="<?php echo ( FMWP()->common()->topic()->is_trashed( $fmwp_topic['id'] ) ) ? 1 : 0; ?>"
					data-locked="<?php echo ( FMWP()->common()->topic()->is_locked( $fmwp_topic['id'] ) ) ? 1 : 0; ?>"
					data-pinned="<?php echo ( FMWP()->common()->topic()->is_pinned( $fmwp_topic['id'] ) ) ? 1 : 0; ?>">
					<?php
					$header_classes = array( 'fmwp-topic-base-header' );
					if ( ! ( is_user_logged_in() && count( $topic_dropdown_items ) > 0 ) ) {
						$header_classes[] = 'fmwp-topic-no-actions';
					}
					?>
					<div class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>">
						<div class="fmwp-topic-avatar">
							<a href="<?php echo esc_url( FMWP()->user()->get_profile_link( $topic->post_author ) ); ?>" data-fmwp_tooltip="<?php echo esc_attr( FMWP()->user()->generate_card( $topic->post_author ) ); ?>" data-fmwp_tooltip_id="fmwp-user-card-tooltip">
								<?php echo wp_kses( FMWP()->user()->get_avatar( $topic->post_author, 'inline', 60 ), FMWP()->get_allowed_html( 'templates' ) ); ?>
							</a>
						</div>
						<div class="fmwp-topic-data">
							<div class="fmwp-topic-data-top">
								<span class="fmwp-topic-data-head">
									<span class="fmwp-topic-data-head-section">
										<?php // translators: %s is the user display name. ?>
										<a href="<?php echo esc_url( $author_link ); ?>" title="<?php echo esc_attr( sprintf( __( '%s Profile', 'forumwp' ), FMWP()->user()->display_name( $author ) ) ); ?>">
											<?php foreach ( FMWP()->common()->topic()->status_markers as $class => $data ) { ?>
												<span class="fmwp-topic-status-marker <?php echo esc_attr( $class ); ?> fmwp-tip-n"
													title="<?php echo esc_attr( $data['title'] ); ?>">
													<i class="<?php echo esc_attr( $data['icon'] ); ?>"></i>
												</span>
											<?php } ?>
											<?php echo esc_html( FMWP()->user()->display_name( $author ) ); ?>
										</a>

										<?php
										$topic_author_tags = FMWP()->common()->topic()->get_author_tags( $topic );
										if ( count( $topic_author_tags ) ) {
											?>
											<span class="fmwp-topic-author-tags-wrapper fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">
												<?php foreach ( $topic_author_tags as $author_tag ) { ?>
													<span class="fmwp-topic-tag <?php echo ! empty( $author_tag['class'] ) ? esc_attr( $author_tag['class'] ) : ''; ?>">
														<?php echo esc_html( $author_tag['title'] ); ?>
													</span>
													<?php
												}

												FMWP()->get_template_part( 'topic-status-tags' );
												?>

											</span>
										<?php } ?>
									</span>

									<span class="fmwp-topic-subdata">
										<?php
										$last_upgrade = '';
										if ( ! FMWP()->common()->topic()->is_pending( $topic->ID ) ) {
											$last_upgrade         = get_post_meta( $topic->ID, 'fmwp_last_update', true );
											$default_last_upgrade = ( ! empty( $topic->post_modified_gmt ) && '0000-00-00 00:00:00' !== $topic->post_modified_gmt ) ? human_time_diff( strtotime( $topic->post_modified_gmt ) ) : '';
											$last_upgrade         = ! empty( $last_upgrade ) ? human_time_diff( $last_upgrade ) : $default_last_upgrade;
										}
										echo esc_html( $last_upgrade );
										?>
									</span>
								</span>

								<?php if ( is_user_logged_in() && count( $topic_dropdown_items ) > 0 ) { ?>
									<span class="fmwp-topic-top-actions">
										<span class="fmwp-topic-top-actions-dropdown" title="<?php esc_attr_e( 'More Actions', 'forumwp' ); ?>">
											<i class="fas fa-angle-down"></i>
										</span>
									</span>

									<?php
									//Topic dropdown actions
									FMWP()->frontend()->shortcodes()->dropdown_menu( '.fmwp-topic-top-actions-dropdown', 'click', $topic_dropdown_items );
								}
								?>
							</div>
							<div class="fmwp-topic-data-content">
								<?php echo wp_kses( $topic->post_content, FMWP()->get_allowed_html( 'templates' ) ); ?>
							</div>
						</div>
					</div>
					<div class="fmwp-topic-base-footer">
						<div class="fmwp-topic-left-panel">
							<?php
							if ( is_user_logged_in() ) {
								if ( FMWP()->user()->can_reply( $topic->ID ) ) {
									?>
									<input type="button" class="fmwp-write-reply" title="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" />
									<span class="fmwp-topic-closed-notice"><?php esc_html_e( 'This topic is closed to new replies', 'forumwp' ); ?></span>
									<?php
								} else {
									echo wp_kses( apply_filters( 'fmwp_reply_disabled_reply_text', '<span class="fmwp-topic-closed-notice">' . esc_html__( 'This topic is closed to new replies', 'forumwp' ) . '</span>', $topic->ID ), FMWP()->get_allowed_html( 'templates' ) );
								}
							} elseif ( 'publish' === $topic->post_status ) {
								?>
								<input type="button" class="<?php echo esc_attr( $unlogged_class ); ?>" title="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" data-fmwp_popup_title="<?php esc_attr_e( 'Login to reply to this topic', 'forumwp' ); ?>" />
								<?php
							}
							?>
							<?php
							$sort_wrapper_classes = array(
								'fmwp-topic-sort-wrapper',
								'fmwp-responsive',
							);
							if ( $replies_count < 2 ) {
								$sort_wrapper_classes[] = 'fmwp-topic-hidden-sort';
							}
							?>
							<span class="<?php echo esc_attr( implode( ' ', $sort_wrapper_classes ) ); ?> fmwp-ui-xs">
								<label>
									<span><?php esc_html_e( 'Sort:', 'forumwp' ); ?>&nbsp;</span>
									<select class="fmwp-topic-sort" autocomplete="off">
										<?php foreach ( FMWP()->common()->reply()->sort_by as $key => $sort_title ) { ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $fmwp_topic['order'], $key ); ?>><?php echo esc_html( $sort_title ); ?></option>
										<?php } ?>
									</select>
								</label>
							</span>
						</div>
						<div class="fmwp-topic-right-panel">

							<?php do_action( 'fmwp_topic_footer', $fmwp_topic['id'], $fmwp_topic ); ?>

							<span class="<?php echo esc_attr( implode( ' ', $sort_wrapper_classes ) ); ?> fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">
								<label>
									<span><?php esc_html_e( 'Sort:', 'forumwp' ); ?>&nbsp;</span>
									<select class="fmwp-topic-sort" autocomplete="off">
										<?php foreach ( FMWP()->common()->reply()->sort_by as $key => $sort_title ) { ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $fmwp_topic['order'], $key ); ?>><?php echo esc_html( $sort_title ); ?></option>
										<?php } ?>
									</select>
								</label>
							</span>
						</div>
					</div>
				</div>

				<div class="clear"></div>
				<div class="fmwp-topic-wrapper" data-fmwp_topic_id="<?php echo esc_attr( $fmwp_topic['id'] ); ?>"
					data-order="<?php echo esc_attr( $fmwp_topic['order'] ); ?>">
				</div>
			</div>

			<div class="fmwp-topic-footer">
				<?php
				if ( is_user_logged_in() ) {
					if ( FMWP()->user()->can_reply( $topic->ID ) ) {
						?>
						<input type="button" class="fmwp-write-reply" title="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" />
						<span class="fmwp-topic-closed-notice"><?php esc_html_e( 'This topic is closed to new replies', 'forumwp' ); ?></span>
						<?php
					} else {
						echo wp_kses( apply_filters( 'fmwp_reply_disabled_reply_text', '<span class="fmwp-topic-closed-notice">' . __( 'This topic is closed to new replies', 'forumwp' ) . '</span>', $topic->ID ), FMWP()->get_allowed_html( 'templates' ) );
					}
				} elseif ( 'publish' === $topic->post_status ) {
					?>
					<input type="button" class="<?php echo esc_attr( $unlogged_class ); ?>" title="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" data-fmwp_popup_title="<?php esc_attr_e( 'Login to reply to this topic', 'forumwp' ); ?>" />
					<?php
				}
				?>
			</div>
		<?php } ?>
		<div class="clear"></div>
	</div>

	<div class="clear"></div>

	<?php
	//Reply dropdown actions
	FMWP()->frontend()->shortcodes()->dropdown_menu( '.fmwp-reply-top-actions-dropdown', 'click' );

	wp_reset_postdata();
}
