<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class AgentMoneyList extends Model
{
    public function getSource() {
        return "agent_money_list";
    }
}
