<?php if (!defined('ABSPATH')) exit;
/* @var bool $showEmptyFields */
/* @var Quform_Admin_Page_Entries_View $page */
/* @var Quform_Options $options */
?><div id="top" class="qfb qfb-cf">
    <?php echo $page->getNavHtml(array('id' => $form->getId(), 'name' => $form->config('name'))); ?>

    <?php if (current_user_can('quform_edit_entries')) : ?>
        <div class="qfb-fixed-buttons">
            <a href="<?php echo esc_url(add_query_arg(array('sp' => 'edit'))); ?>" title="<?php esc_attr_e('Edit', 'quform'); ?>"><i class="fa fa-pencil"></i></a>
        </div>
    <?php endif; ?>

    <div class="qfb-cf qfb-entry-wrap">
        <div class="qfb-entry-left">
            <div class="qfb-box">
                <div class="qfb-entry-show-empty-wrap"><form><label><input type="checkbox" value="1" <?php checked($showEmptyFields, '1'); ?> name="show_empty_fields" id="qfb-entry-show-empty-fields"> <?php esc_html_e('Show empty fields', 'quform'); ?></label></form></div>
                <h3 class="qfb-entry-heading qfb-settings-heading"><i class="mdi mdi-message"></i><?php esc_html_e('Submitted form data', 'quform'); ?></h3>
                <table class="qfb-entry-table">
                    <?php
                    foreach ($form->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
                        if ( ! $element instanceof Quform_Element_Field && ! $element instanceof Quform_Element_Container && ! $element instanceof Quform_Element_Html) {
                            continue;
                        }

                        // Skip hidden elements
                        if ($element->isHidden()) {
                            continue;
                        }

                        if ($element instanceof Quform_Element_Html) {
                            if ($element->config('showInEntry')) {
                                echo sprintf('<tr class="qfb-entry-row-html"><td colspan="2">%s</td></tr>', $element->getContent());
                            }

                            continue;
                        }

                        // Skip empty elements
                        if ($element->isEmpty() && ! $showEmptyFields) {
                            continue;
                        }

                        if ($element instanceof Quform_Element_Group) {
                            if ($element->config('showLabelInEntry') && Quform::isNonEmptyString($element->config('label'))) {
                                echo sprintf(
                                    '<tr colspan="2" class="qfb-entry-row-%s"><th><div class="qfb-entry-group-head">%s</div></th></tr>',
                                    $element->config('type'),
                                    Quform::escape($element->config('label'))
                                );
                            }
                        } else if ($element instanceof Quform_Element_Field) {
                            if ($element->config('saveToDatabase')) {
                                echo sprintf('<tr><th><div class="qfb-entry-element-label">%s</div></th></tr>', Quform::escape($element->getAdminLabel()));
                                echo sprintf('<tr><td>%s</td></tr>', $element->getValueHtml());
                            }
                        }
                    }
                    ?>
                </table>
            </div>
        </div>
        <div class="qfb-entry-right">
            <div class="qfb-box">
                <h3 class="qfb-entry-heading qfb-settings-heading"><i class="mdi mdi-announcement"></i><?php esc_html_e('Additional information', 'quform'); ?></h3>
                <table class="qfb-entry-table">
                    <tr>
                        <th><?php esc_html_e('Form', 'quform'); ?></th>
                        <td>
                            <?php
                                printf(
                                    '<a href="%s">%s</a>',
                                    esc_url(admin_url(sprintf('admin.php?page=quform.forms&sp=edit&id=%d', $form->getId()))),
                                    Quform::isNonEmptyString($form->config('name')) ? esc_html($form->config('name')) : esc_html__('(no title)', 'quform')
                                );
                            ?>
                        </td>
                    </tr>
                    <?php
                        $keys = array(
                            'created_at' => __('Date', 'quform'),
                            'id' => __('Entry ID', 'quform'),
                            'form_url' => __('Form URL', 'quform'),
                            'referring_url' => __('Referring URL', 'quform'),
                            'post_id' => __('Page', 'quform'),
                            'created_by' => __('User', 'quform'),
                            'ip' => __('IP address', 'quform')
                        );

                        foreach ($keys as $key => $label) {
                            if (($value = Quform::get($entry, $key)) && Quform::isNonEmptyString($value) || $showEmptyFields) {
                                if (Quform::isNonEmptyString($value)) {
                                    switch ($key) {
                                        case 'created_at':
                                            $value = $options->formatDate($value);
                                            break;
                                        case 'form_url':
                                        case 'referring_url':
                                            $value = '<a href="' . esc_url($value) . '" target="_blank">' . esc_html($value) . '</a>';
                                            break;
                                        case 'created_by':
                                            $user = get_user_by('id', $value);

                                            if ($user instanceof WP_User) {
                                                $link = get_edit_user_link($user->ID);

                                                if ( ! empty($link)) {
                                                    $value = '<a href="' . esc_url($link) . '" title="' . esc_attr('View user profile', 'quform') . '" target="_blank">' . esc_html($user->user_login) . '</a>';
                                                } else {
                                                    $value = esc_html($user->user_login);
                                                }
                                            } else {
                                                continue;
                                            }
                                            break;
                                        case 'post_id':
                                            $post = get_post($value);

                                            if ($post instanceof WP_Post) {
                                                $link = get_permalink($post->ID);

                                                if ( ! empty($link)) {
                                                    $value = '<a href="' . esc_url($link) . '" title="' . esc_attr('View page', 'quform') . '" target="_blank">' . esc_html(get_the_title($post->ID)) . '</a>';
                                                } else {
                                                    $value = esc_html(get_the_title($post->ID));
                                                }
                                            } else {
                                                continue;
                                            }
                                            break;
                                        default:
                                            $value = esc_html($value);
                                            break;
                                    }
                                }

                                echo '<tr><th>' . esc_html($label) . '</th><td>' . $value .'</td></tr>';
                            }
                        }
                    ?>
                    <?php if (count($labels)) : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e('Labels', 'quform'); ?></th>
                            <td class="qfb-single-entry-labels" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
                                <?php echo $page->getEntryLabelsHtml($entry['labels']); ?>
                                <div id="qfb-entry-label-set">
                                    <?php foreach ($labels as $label) : ?>
                                        <div class="qfb-entry-label" data-label="<?php echo Quform::escape(wp_json_encode($label)); ?>" style="background-color: <?php echo Quform::escape($label['color']); ?>;"><span class="qfb-entry-label-name"><?php echo Quform::escape($label['name']); ?></span><i class="fa fa-check"></i></div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <?php if (current_user_can('quform_edit_entries')) : ?>
        <a href="<?php echo esc_url(add_query_arg(array('sp' => 'edit'))); ?>" class="qfb-button qfb-button-blue"><i class="fa fa-pencil"></i><?php esc_html_e('Edit', 'quform'); ?></a>
    <?php endif; ?>
</div>
