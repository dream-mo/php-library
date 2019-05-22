#### 文件内容

##### SimpleMemcached.php

> 包含SimpleMemcached类 以及 BasicMemcached类

demo如下:

```php

try {
    $simpleMem = new SimpleMemcached('localhost',11211);
    
    // 1.add操作
    $res = $simpleMem->add('name','孙悟空',600);
    
    if ($res) { 
       
       // 2.get操作
       var_dump("name value is: ".$simpleMem->get('name'));
    }
    
    // 3.replace操作
    $res = $simpleMem->replace('name', '斗战胜佛', 600);
    
    // 4.delete操作
    $res = $simple->delete('name');
    
} catch (Exception $e) {
    echo $e->getMessage();
}

```