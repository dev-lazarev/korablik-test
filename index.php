<?php

class Requester
{

    public function http_response($url)
    {
        if ($this->urlValidate($url)) {
            try {
                $ch = curl_init();
                if (FALSE === $ch)
                    throw new \Exception('failed to initialize');
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                //curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
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
                    return $body;
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


abstract class Work
{
    protected $max = 10;
    protected $counter = 10;


    final public function replaceFromParams($search, $replace, $text)
    {
        $text = $this->replace($search, $replace, $text);
        if (strpos($text, $search)) {
            $this->counter--;
            if ($this->counter > 0) {
                $text = $this->replaceFromParams($search, $replace, $text);
            }
        }
        $this->counter = $this->max;
        return $text;
    }


    final public function replaceFromArray($array, $text)
    {
        foreach ($array as $search => $replace) {
            $this->replaceFromParams($search, $replace, $text);
        }
        return $text;
    }


    abstract protected function replace($search, $replace, $text);

}

class Replacer extends Work
{
    protected function replace($search, $replace, $text)
    {
        return str_replace($search, $replace, $text);
    }

}

class RevertReplacer extends Work
{

    protected function replace($replace, $search, $text)
    {
        return str_replace($search, $replace, $text);
    }
}


class Command
{
    /**
     * @var Requester
     */
    private $requester;
    /**
     * @var Work
     */
    private $worker;

    protected $text;
    protected $url;

    public function __construct(Requester $requester, Work $work, $url = '')
    {
        $this->requester = $requester;
        $this->worker = $work;
        if (!empty($url)) {
            $this->text = $requester->http_response($url);
        }
    }

    public function setUrl($url)
    {
        $this->text = $this->http_response($url);
    }


    public function setWorker(Work $worker)
    {
        $this->worker = $worker;
    }

    public function replaceFromParams($search, $replace)
    {
        if ($this->worker == null) {
            throw new Exception('worker null');
        }
        $this->text = $this->worker->replaceFromParams($search, $replace, $this->text);
    }

    public function replaceFromArray($array)
    {
        if ($this->worker == null) {
            throw new Exception('worker null');
        }
        $this->text = $this->worker->replaceFromArray($array, $this->text);
    }

    public function getText()
    {
        return $this->text;
    }


}


$command = new Command(new Requester, new Replacer(), 'http://ya.ru');
$command->replaceFromParams('Яндекс', 'не Яндекс');
$command->setWorker(new RevertReplacer());
echo $command->getText();
$command->replaceFromParams('Яндекс', 'не Яндекс');
echo $command->getText();
