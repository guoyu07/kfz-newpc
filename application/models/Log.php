<?php
use http\Request;
use kongfz\Model;
use log\File;

/**
 * Class LogModel
 * 运营后台操作日志
 * @author  liubang <liubang@kongfz.com>
 */
class LogModel extends Model {

    /** @var string 当前model操作的数据库 */
    private static $database = 'adminMaster';

    /** @var string 当前model操作的表 */
    private static $table = 'log';

    /** @var null | \Medoo\Medoo */
    private $logDb = null;

    use \kongfz\traits\Singleton;

    /**
     * @return LogModel
     */
    public static function singleton() {
        return self::instance();
    }

    private function _init_() {
        $this->logDb = storage\Db::factory(self::$database);
    }

    /**
     * 将运营操作记录到log表中
     *
     * @param string $opcode 操作码
     * @param string $optext 操作描述
     *
     * @return bool
     */
    public function write($opcode, $optext = '') {
        if (empty($opcode)) {
            return false;
        }
        $data = [
            'adminId'  => isset($_SESSION['adminId']) ? $_SESSION['adminId'] : '0',
            'username' => isset($_SESSION['username']) ? $_SESSION['username'] : '',
            'ip'       => Request::getClientIp(),
            'optime'   => time(),
            'opcode'   => $opcode,
            'optext'   => $optext
        ];
        try {
            $this->logDb->insert(self::$table, $data);
        } catch (Exception $e) {
            try {
                $log = new File('logerror', '/data/logs/scripts/newpc/' . date("Y/md/") . __METHOD__ . '.log');
                $log->info($opcode . ':' . $optext, $data);
                $log->error($e->getMessage());
            } catch (Exception $e) {
                ;
            }
        }
    }
}