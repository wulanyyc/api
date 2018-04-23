<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class NotifyMessage extends Model
{
    public function getSource() {
        return "notify_message";
    }
}
