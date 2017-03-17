<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/1 下午2:33
 | @copyright : (c) kongfz.com
 | @license   : MIT (http://opensource.org/licenses/MIT)
 |------------------------------------------------------------------
 */

class IsbnController extends \kongfz\Controller
{
    public function init() {
        parent::init();
    }

    public function getItemInfoByIsbnAction() {
        //TODO complete the code.
        \http\Response::json(true, []);
    }
}