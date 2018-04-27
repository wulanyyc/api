<?php
use Biaoye\Model\Customer;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\Agent;
use Biaoye\Model\AgentOrderSuc;
use Biaoye\Model\AgentOrderList;
use Phalcon\Mvc\Model\Transaction\Manager;

class JobTask extends \Phalcon\CLI\Task
{
    public function assignAction()
    {
        $hour = date('H', time());
        $switch = $this->di->get('config')->params->switch_day_night;

        // if ($hour >= $switch) {
            $data = CustomerOrder::find([
                "conditions" => "status=1 and date=" . date('Ymd', time()),
                "columns" => 'id, customer_id',
                "order" => 'id asc',
                "limit" => 50,
            ])->toArray();

            if (empty($data)) {
                exit;
            }

            foreach($data as $item) {
                echo $this->handleJob($item['id'], $item['customer_id']);
            }
        // }
    }

    private function handleJob($oid, $cid) {
        $info = Customer::findFirst($cid);

        $agents = Agent::find([
            "conditions" => "status=1 and school_id=" . $info->school_id . " and room_id=" . $info->room_id . " and manager_flag=0",
            "columns" => 'id',
        ])->toArray();

        if (empty($agents)) {
            return ;
        }

        $ids = [];
        foreach($agents as $agent) {
            $ids[] = $agent['id'];
        }

        $randId = rand(1, count($ids));
        $assignId = $ids[$randId - 1];

        try {
            $manager = new Manager();
            $transaction = $manager->get();

            $ar = new AgentOrderSuc();
            $ar->setTransaction($transaction);

            $ar->agent_id = $assignId;
            $ar->order_id = $oid;
            $ar->date = date('Ymd', time());

            if (!$ar->save()) {
                $transaction->rollback("save agent_order_suc fail");
            }

            $co = CustomerOrder::findFirst($oid);
            $co->setTransaction($transaction);
            $co->status = 2;

            if (!$co->save()) {
                $transaction->rollback("save customer_order fail");
            }

            $transaction->commit();

            // 删除抢单key
            $redisKey = $this->di->get('config')->params->get_order_prefix . $oid;
            $this->di->get('redis')->delete($redisKey);
        } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            AgentOrderList::addRecord($assignId, $oid);

            $msg = $e->getMessage();
            $app->logger->error("assign job db save fail_" . $assignId . '_' . $oid . ':' . $msg);

            return 0;
        }

        AgentOrderList::addRecord($assignId, $oid, 1);

        return 1;
    }
}
