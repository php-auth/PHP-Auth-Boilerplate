<?php
/**
 * App
 *
 * @version 1.0.0
 */
namespace Layer\Controller;

class App
{
    /**
     * Get requested page view
     *
     * @param string $name
     * @param array $data
     * @return mixed
     * @access public
     */
    public function screen($name, $data = []): mixed
    {
        return view($name, $data);
    }
}
