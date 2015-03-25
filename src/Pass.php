<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 4.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use League\Url\Interfaces\ComponentInterface;

/**
 *  A class to manipulate URL Pass component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Pass extends AbstractComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $value = $this->__toString();

        if ('' !== $value) {
            $value = ':'.$value;
        }

        return $value;
    }
}
