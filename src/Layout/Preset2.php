<?php
namespace Jankx\PostLayout\Layout;

use Jankx\PostLayout\PostLayout;

class Preset2 extends PostLayout
{
    const LAYOUT_NAME = 'preset-2';

    protected $supportColumns = false;

    public function get_name()
    {
        return static::LAYOUT_NAME;
    }

    public static function get_layout_label()
    {
        return sprintf(__('Preset %d', 'jankx'), 2);
    }

    protected function defaultOptions()
    {
        return array(
            'show_thumbnail' => true,
            'thumbnail_position' => 'left',
            'show_excerpt' => false,
        );
    }

    public function render($echo = true)
    {
        if (!$this->templateEngine) {
            error_log(__('The template engine is not setted to render content', 'jankx_post_layout'));
            return;
        }
        if (!$echo) {
            ob_start();
        }
        foreach ((array)$this->wp_query->query_vars['post_type'] as $post_type) {
            // This hook use to stop custom render post layout
            do_action("jankx/layout/{$post_type}/loop/init", $this->get_name(), $this);
        }
        ?>
        <div class="jankx-posts-layout preset-2">
        <div class="jankx-posts-layout-inner top-list bottom-large">
                <?php
                // Create post list
                $this->loop_start(true);

                while ($this->checkNextPost()) {
                    if ($this->wp_query->current_post === 3) {
                        break;
                    }
                    $this->the_post();
                    $this->renderLoopItem(
                        $this->getCurrentPostItem()
                    );
                }

                $this->loop_end(true);

                // Create first post
                $this->the_post();

                $post = $this->getCurrentPostItem();
                // Setup the post classes
                $this->createCustomPostClass($post);

                $this->templateEngine->render(array(
                    $post->post_type . '-layout/preset2/large-item',
                    'post-layout/preset2/large-item',
                    'post-layout/large-item',
                ));

                wp_reset_postdata();
                ?>
            </div>

            <div class="jankx-posts-layout-inner top-large bottom-list">
                <?php
                // Create first post
                $this->the_post();

                $post = $this->getCurrentPostItem();
                // Setup the post classes
                $this->createCustomPostClass($post);

                $this->templateEngine->render(array(
                    $post->post_type . '-layout/preset2/large-item',
                    'post-layout/preset2/large-item',
                    'post-layout/large-item',
                ));

                // Create post list
                $this->loop_start(true);

                while ($this->checkNextPost()) {
                    if ($this->wp_query->current_post > 0 && $this->wp_query->current_post % 4 === 0) {
                        break;
                    }
                    $this->the_post();
                    $this->renderLoopItem(
                        $this->getCurrentPostItem()
                    );
                }

                $this->loop_end(true);
                ?>
            </div>



            <?php if (array_get($this->options, 'show_paginate', false)) : ?>
                <?php echo jankx_paginate(); ?>
            <?php endif; ?>
        </div>
        <?php
        if (!$echo) {
            return ob_get_clean();
        }
    }
}
