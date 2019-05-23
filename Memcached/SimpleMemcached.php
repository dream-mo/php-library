<?php
/**
 * Created by PhpStorm.
 * User: mojun
 * Email: dreammovip@163.com
 * Date: 2019/5/22
 * Time: 23:26
 */

/**
 * Class SimpleMemcached
 *
 * 基于Memcached扩展 简单操作Memcached类
 *
 * @method boolean add() add($key, $val, $expires=0)
 * @method boolean delete() delete($key, $time=0)
 * @method boolean set() set($key, $val, $expires=0)
 * @method boolean replace() replace($key, $val, $expires=0)
 * @method mixed get() get($key)
 * @method boolean incr() incr($key, $incrIntVal=1)
 * @method boolean decr() decr($key, $decrIntVal=1)
 * @method boolean append() append($key, $appendVal)
 * @method boolean prepend() prepend($key, $prependVal)
 * @method string getVersion() getVersion()
 * @method Memcached getMemcachedInstance getMemcachedInstance()
 *
 */
class SimpleMemcached extends BasicMemcached
{
    /**
     * @var Memcached|null
     *
     * memcached对象
     */
    private $memcached = null;

    /**
     * @var array|null
     *
     * 服务端版本号
     */
    private $version = null;

    /**
     * @var bool
     *
     * 发送warning或者错误是否throw异常
     *
     */
    private $debug = true;

    public function __construct($host = '', $port = 11211, $debug = true)
    {
        if ( !class_exists('Memcached') ) {
            throw new Exception('Please install Memcached extension');
        }

        $this->memcached = new Memcached();
        $this->memcached->addServer($host,$port);
        $this->debug = boolval($debug);

        // 尝试通过getVersion来判断 是否能够正常连接到memcached服务器
        if (($version = $this->memcached->getVersion()) != false) {
            $this->version = $version;
        } else {
            $this->handleException();
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Exception
     *
     * 统一处理异常
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            $returnVal = $this->$name(...$arguments);
            $this->handleException();
            return $returnVal;
        } else {
            throw new Exception("call undfiend method named $name");
        }
    }

    /**
     * @throws Exception
     *
     * 抛出错误异常
     */
    private function handleException()
    {
        if ($this->debug) {
            $code = $this->memcached->getLastErrorCode();
            if ($code) {
                $message  = $this->memcached->getLastErrorMessage();
                $message = "Error code $code : ".$message;
                throw new Exception($message, $code);
            }
        }
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function __destruct()
    {
        // 关闭所有打开的连接
        $this->memcached->quit();
    }
}

/**
 * Class BasicMemcached
 *
 * 实现类 SimpleMemcached通过extends继承后 可以调用父类的protected方法。因为
 * SimpleMemcache类要统一处理每次调用后，可能会产生的异常，用硬编码每次去调用同一个函数不太优雅。这样做之后，只要在
 * SimpleMemcache上加上注解，增强IDE的代码可读性即可。 通过__call方法来实现，归一化处理异常问题。
 *
 */
class BasicMemcached
{

    /**
     * @param $key
     * @param $val
     * @param int $expires
     * @return mixed
     *
     * 添加新的key
     *
     *
     */
    protected function add($key, $val, $expires=0)
    {
        return $this->memcached->add($key, $val, $expires);
    }

    /**
     * @param $key
     * @param int $time
     * @return mixed
     *
     * 删除key
     *
     */
    protected function delete($key, $time=0)
    {
        return $this->memcached->delete($key,$time);
    }

    /**
     * @param $key
     * @param $val
     * @param int $expires
     * @return mixed
     *
     * 存在key则替换 否则 新增key
     */
    protected function set($key, $val, $expires=0)
    {
        return $this->memcached->set($key, $val, $expires);
    }

    /**
     * @param $key
     * @param $val
     * @param int $expires
     * @return mixed
     *
     * 替换key的value. 前提是key要存在，否则可能有提示错误
     */
    protected function replace($key, $val, $expires=0)
    {
        return $this->memcached->replace($key, $val, $expires=0);
    }

    /**
     * @param $key
     * @return mixed
     *
     * 根据key获取值
     *
     */
    protected function get($key)
    {
        return $this->memcached->get($key);
    }

    /**
     * @param $key
     * @param int $incrIntVal
     * @return mixed
     *
     * 对数字进行加操作
     *
     */
    protected function incr($key, $incrIntVal=1)
    {
        return $this->memcached->increment($key, $incrIntVal);
    }

    /**
     * @param $key
     * @param int $decrIntVal
     * @return mixed
     *
     * 对数字进行减操作
     */
    protected function decr($key, $decrIntVal=1)
    {
        return $this->memcached->decrement($key, $decrIntVal);
    }

    /**
     * @param $key
     * @param $appendVal
     * @return mixed
     *
     * 尾部追加内容
     */
    protected function append($key, $appendVal)
    {
        $this->memcached-> setOption(Memcached::OPT_COMPRESSION, false);

        return $this->memcached->append($key, $appendVal);
    }


    /**
     * @param $key
     * @param $prependVal
     * @return mixed
     *
     * 头部追加内容
     */
    protected function prepend($key, $prependVal)
    {
        $this->memcached->setOption(Memcached::OPT_COMPRESSION, false);

        return $this->memcached->prepend($key, $prependVal);
    }

    /**
     * @return mixed
     *
     * 返回当前服务器的版本
     */
    protected function getVersion()
    {
        return $this->version;
    }

    /**
     * @return mixed
     *
     * 返回 Memcached实例
     */
    protected function getMemcachedInstance()
    {
        return $this->memcached;
    }

    /**
     * BasicMemcached constructor.
     *
     * 不能构造出来 只能被继承的子类实现
     */
    private function __construct()
    {

    }

    /**
     * 防止clone本类
     */
    private function __clone()
    {

    }

    /**
     * 防止从反序列化得到本类的实例
     */
    private function __wakeup()
    {

    }
}
