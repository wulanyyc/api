<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;

class CompanyInventory extends Model
{
    public function getSource() {
        return "company_inventory";
    }
}
