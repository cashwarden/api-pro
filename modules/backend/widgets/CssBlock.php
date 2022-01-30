<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\backend\widgets;

use yii\widgets\Block;

class CssBlock extends Block
{
    /**
     * @var null
     */
    public $key = null;
    /**
     * @var array the HTML attributes for the style tag.
     */
    public $options = [];

    /**
     * Ends recording a block.
     * This method stops output buffering and saves the rendering result as a named block in the view.
     * @throws \Exception
     */
    public function run()
    {
        $block = ob_get_clean();
        if ($this->renderInPlace) {
            throw new \Exception('not implemented yet ! ');
            // echo $block;
        }
        // $block = trim($block) ;
        $block = static::unwrapStyleTag($block);

        $this->view->registerCss($block, $this->options, $this->key);
    }

    /**
     * @param $cssBlock
     * @return string
     */
    public static function unwrapStyleTag($cssBlock)
    {
        $block = trim($cssBlock);
        /*
        $jsBlockPattern  = '|^<script[^>]*>(.+?)</script>$|is';
        if(preg_match($jsBlockPattern,$block)){
            $block =  preg_replace ( $jsBlockPattern , '${1}'  , $block );
        }
        */
        $cssBlockPattern = '|^<style[^>]*>(?P<block_content>.+?)</style>$|is';
        if (preg_match($cssBlockPattern, $block, $matches)) {
            $block = $matches['block_content'];
        }
        return $block;
    }
}
