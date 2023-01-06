<?php
/**
 * Class SampleTest
 *
 * @package Sample_Plugin
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase
{
    /**
     * A single example test.
     */
    public function test_if_actions_are_registered()
    {
        $callback = function () {
            echo 'just added action to init';
        };
        add_action('init', $callback);

        $hasAction = has_action('init', $callback);
        $this->assertTrue(is_int($hasAction) || $hasAction);
    }
}