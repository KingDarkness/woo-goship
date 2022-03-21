<?php
namespace App\Model;

use Kingdarkness\Goship\Arr;

class GsPickup
{
    const TABLE_NAME = 'woo_goship_pickups';

    public static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'woo_goship_pickups';
    }

    public static function initTable()
    {
        /*create database*/
        global $wpdb;
        global $jalDbVer;

        $pickupTable = self::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("show tables like '$pickupTable'") != $pickupTable) {
            $sql = "CREATE TABLE $pickupTable (
                `id` mediumint(9) NOT NULL AUTO_INCREMENT,
                `shop_name` varchar(255) NULL,
                `name` varchar(255) NOT NULL,
                `phone` varchar(255) NOT NULL,
                `street` varchar(255) NOT NULL,
                `city_code` varchar(255) NOT NULL,
                `district_code` varchar(255) NOT NULL,
                `ward_id` mediumint(9) NOT NULL,
                `full_street` varchar(255) NOT NULL,
                `default` tinyint(4) DEFAULT 0,
                PRIMARY KEY  (id)
            ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        add_option('jal_db_version', $jalDbVer);
        /*end create database*/
    }

    public function getByQuery($params = [])
    {
        global $wpdb;
        $tableName = self::getTableName();

        $query          = "SELECT * FROM $tableName";
        if (Arr::get($params, 'default')) {
            $query .= ' where `default` = 1';
        }
        $total_query    = "SELECT COUNT(1) FROM (${query}) AS combined_table";
        $total          = $wpdb->get_var($total_query);
        $items_per_page = abs((int) Arr::get($params, 'per_page', 20));
        $page           = abs((int) Arr::get($params, 'page', 1));
        $offset         = ($page * $items_per_page) - $items_per_page;
        $results         = $wpdb->get_results($query . " ORDER BY id DESC LIMIT ${offset}, ${items_per_page}");
        $totalPage      = ceil($total / $items_per_page);

        return [
            'data' => $results,
            'pagination' => ['total' => $total, 'items_per_page' => $items_per_page, 'total_page' => $totalPage, 'current_page' => $page]
        ];
    }

    public function create(array $data)
    {
        global $wpdb;
        $tableName = self::getTableName();

        $dataStore = [];
        $dataStore['shop_name'] = Arr::get($data, 'shop_name');
        $dataStore['name'] = $data['name'];
        $dataStore['phone'] = $data['phone'];
        $dataStore['street'] = $data['street'];
        $dataStore['city_code'] = $data['city_code'];
        $dataStore['district_code'] = $data['district_code'];
        $dataStore['ward_id'] = $data['ward_id'];
        $dataStore['full_street'] = $data['full_street'];
        $dataStore['default'] = Arr::get($data, 'default', false) ? 1 : 0;

        $result = $wpdb->insert($tableName, $dataStore);
        $lastId = $wpdb->insert_id;
        if ($result) {
            if ($data['default']) {
                $wpdb->query($wpdb->prepare(
                    "
                    UPDATE $tableName
                    SET `default` = 0
                    WHERE id <> $lastId
                    "
                ));
            }
            return $this->getById($lastId);
        }
        return false;
    }

    public function update($id, array $data)
    {
        global $wpdb;
        $tableName = self::getTableName();

        $dataStore = [];
        $dataStore['shop_name'] = Arr::get($data, 'shop_name');
        $dataStore['name'] = $data['name'];
        $dataStore['phone'] = $data['phone'];
        $dataStore['street'] = $data['street'];
        $dataStore['city_code'] = $data['city_code'];
        $dataStore['district_code'] = $data['district_code'];
        $dataStore['ward_id'] = $data['ward_id'];
        $dataStore['full_street'] = $data['full_street'];
        $dataStore['default'] = Arr::get($data, 'default', false) ? 1 : 0;

        $result = $wpdb->update($tableName, $dataStore, ['id' => $id]);
        if ($result) {
            if ($data['default']) {
                $wpdb->query($wpdb->prepare(
                    "
                    UPDATE $tableName
                    SET `default` = 0
                    WHERE id <> $id
                    "
                ));
            }
            return $this->getById($id);
        }
        return false;
    }

    public function getById($id)
    {
        global $wpdb;
        $tableName = self::getTableName();
        return $wpdb->get_row("SELECT * FROM $tableName WHERE id = $id");
    }

    public function delete($id)
    {
        global $wpdb;
        $tableName = self::getTableName();

        $pickup = $this->getById($id);
        $result = $wpdb->delete($tableName, [ 'id' => $id ]);
        if ($result && $pickup->default) {
            $row = $wpdb->get_row("SELECT * FROM $tableName ORDER BY id DESC LIMIT 1");
            $wpdb->query($wpdb->prepare(
                "
                UPDATE $tableName
                SET `default` = 1
                WHERE id = $row->id
                "
            ));
        }

        return $result;
    }

    public function getByIdOrDefault($id)
    {
        $pickup = null;
        if ($id) {
            $pickup = $this->getById($id);
        }
        if (!$pickup) {
            global $wpdb;
            $tableName = self::getTableName();
            $pickup = $wpdb->get_row("SELECT * FROM $tableName WHERE `default` = 1 ORDER BY id DESC LIMIT 1");
        }
        return $pickup;
    }
}
