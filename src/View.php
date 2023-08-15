<?php

namespace Realtyna\MvcCore;

class View
{

    protected StartUp $main;

    public function __construct(StartUp $main)
    {
        $this->main = $main;
    }

    /**
     * get a template file
     * EX: slug = admin/index this will look for index.php inside admin folder
     * EX: slug = admin/index & name = user this will look for index-user.php inside admin folder
     * load parameter defines if you want to get location of template or want to
     * @param $slug
     * @param $name
     * @param bool $load
     * @return false|string
     * @since 0.0.1
     */
    function get($slug, string $name = null, bool $load = true)
    {
        // Execute code for this part
        do_action('get_template_part_' . $slug, $slug, $name);

        // Setup possible parts
        $templates = array();
        if (isset($name)) {
            $templates[] = $slug . '-' . $name . '.php';
        }
        $templates[] = $slug . '.php';

        // Allow template parts to be filtered
        $templates = apply_filters('realtyna_get_template_part', $templates, $slug, $name);
        // Return the part that is found
        return $this->locate_template($templates, $load, true);
    }


    /**
     * locates template file
     * @param $template_names
     * @param bool $load
     * @param bool $require_once
     * @return false|string
     * @since 0.0.1
     */
    private function locate_template($template_names, bool $load = false, bool $require_once = true)
    {
        // No file found yet
        $located = false;

        // Try to find a template file
        foreach ((array)$template_names as $template_name) {
            // Continue if template is empty
            if (empty($template_name)) {
                continue;
            }
            global $main;

            // Trim off any slashes from the template name
            $template_name = ltrim($template_name, '/');
            // Check child theme first
            if (file_exists(trailingslashit(get_stylesheet_directory()) . 'templates/realtyna/' . $template_name)) {
                $located = trailingslashit(get_stylesheet_directory()) . 'templates/realtyna/' . $template_name;
                break;
                // Check parent theme next
            } elseif (file_exists(trailingslashit(get_template_directory()) . 'templates/realtyna/' . $template_name)) {
                $located = trailingslashit(get_template_directory()) . 'templates/realtyna/' . $template_name;
                break;
                // Check plugin compatibility last
            } elseif (file_exists(trailingslashit($this->main->config->get('path.view')) . $template_name)) {
                $located = trailingslashit($this->main->config->get('path.view')) . $template_name;
                break;
            }
        }

        if ($load && !empty($located)) {
            load_template($located, $require_once);
        }

        return $located;
    }
}