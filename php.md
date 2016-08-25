```php

abstract class Requester
{

    protected $text;
    protected $max = 10;
    protected $counter = 10;
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    final public function replaceFromParams($search, $replace)
    {
        $this->text = $this->replace($search, $replace);
        if (strpos($this->text, $search)) {
            $this->counter--;
            if ($this->counter > 0) {
                $this->replace($search, $replace);
            }
        } else {
            $this->counter = $this->max;
        }
    }


    final public function replaceFromArray($array)
    {
        foreach ($array as $search => $replace) {
            $this->replaceFromParams($search, $replace);
        }
    }

    abstract protected function replace($search, $replace);

    public function http_response()
    {
        if ($this->urlValidate($this->url)) {
            try {
                $ch = curl_init();
                if (FALSE === $ch)
                    throw new \Exception('failed to initialize');
                curl_setopt($ch, CURLOPT_URL, $this->url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                //curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new \Exception('Service unavailable... try again later...');
                }
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $body = substr($response, $header_size);
                if ($statusCode == 200) {
                    $this->text = $body;
                } else {
                    throw new \Exception($this->getResponse($statusCode));
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            throw new \Exception('url not valid');
        }

    }

    protected function getResponseCodes()
    {
        return [
            '400' => 'Bad request',
            '401' => 'Unauthorized',
            '403' => 'Forbidden',
            '404' => '404',
            '429' => 'Rate limit exceeded',
            '500' => 'Internal server error',
            '503' => 'Service unavailable',
        ];
    }

    protected function urlValidate($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    protected function getResponse($code)
    {
        return $this->getResponseCodes()[$code];
    }
}

class Replacer extends Requester
{
    
    protected function replace($search, $replace)
    {
        return str_replace($search, $replace, $this->text);
    }

}

class RevertReplacer extends Requester{
    
    protected function replace($replace, $search)
    {
        return str_replace($search, $replace, $this->text);
    }
}
```