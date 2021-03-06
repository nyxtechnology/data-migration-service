<?php


namespace DataMigration;


class DataMigration
{
    public static $NO_AUTH = false;
    public static $BASIC_AUTH = 'basic';


    /**
     * Create header to get or post in the curl
     * @param $type
     *  'get' or 'post' type of header
     * @param bool $authType
     * @param null $auth
     * @return array
     */
    protected function createHeader($type, $authType = false, $auth = null) {
        $header = [];
        switch ($type){
            case 'get':
                $header = [
                    "Accept: application/json",
                    "Cache-Control: no-cache",
                    "Connection: keep-alive",
                    "accept-encoding: gzip, deflate",
                    "cache-control: no-cache"
                ];
                break;
            case 'post':
                $header = [
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                ];
                break;
        }

        if ($authType == self::$BASIC_AUTH) {
            $header[] = "Authorization: Basic $auth";
        }

        return $header;
    }

    /**
     * Get data from the source
     * @param $url
     * @param bool $authType
     * @param null $auth
     * @return mixed|null
     */
    public function getData($url, $authType = false, $auth = null) {
        $curl = curl_init();

        $header = $this->createHeader('post', $authType, $auth);

        if ($authType == self::$BASIC_AUTH) {
            $header[] = "Authorization: Basic $auth";
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            // TODO: Add error log
            return null;

        } else {
            return json_decode($response);
        }
    }

    /**
     * Post Data in the destination
     * @param $url
     * @param $data
     * @param bool $authType
     * @param null $auth
     * @return bool|false|string
     */
    public function postData($url, $data, $authType = false, $auth = null) {
        $curl = curl_init();

        $header = $this->createHeader('post', $authType, $auth);

        curl_setopt_array($curl, [
            CURLOPT_URL => "http://api.p2b.local/jsonapi/node/project",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $header,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return false;
        } else {
            return json_encode($response);
        }
    }

    /**
     * Convert data to other structure
     * @param $from
     * @param $to
     * @param $settings
     * @return false|string
     */
    public function fromTo($from, $settings) {

        $data = [];

        foreach ($settings as $property => $value) {
            $data[$property] = $this->getValueFromVariable($value, $from);
        }

        return (object) $data;
    }

    /**
     * Get value from variable. Exemplo: @user.name return the name of the user object.
     * @param $variable
     * @param $from
     * @return string
     */
    private function getValueFromVariable($variable, $from) {
        $return = $variable;
        if (substr($variable,0,1) == '@') {
            // Remove start @ .: @products.product[id=132].name to products.product[id=132].name
            $propertyClean = ltrim($variable, '@');

            // Split variable in .: products.product[id=132].name to ["products","product[id=123]","name"]
            $keys = explode('.', $propertyClean);
            $valueFrom = $from;
            // Foreach in the properties
            foreach ($keys as $key) {
                $filters = [];
                // Check property has filter. From product[id=123] to ["[id=123]","id=123"]
                if (preg_match_all('/\[(.*?)\]/', $key, $filters)) {
                    // Replace filter to get only property. From product[id=123] to product.
                    $prop = preg_replace('/\\[(.*?)\\]/', '', $key);
                    $valueFrom = $valueFrom->$prop;

                    // Search element with the filter criteria. Ex.: id=123
                    $filterValues = explode('=', $filters[1][0]);
                    //TODO: To remove -> is necessary replace filter (within []) and after replace all variable
                    $filterValues[1] = $this->getValueFromVariable(str_replace("->", '.',$filterValues[1]), $from);
                    print_r($filterValues[1]);
                    foreach ($valueFrom as $position => $element) {
                        $checkValue = $filterValues[0];

                        if ($element->$checkValue == $filterValues[1]) {
                            $valueFrom = $valueFrom[$position];
                            break;
                        }
                    }
                } else {
                    $valueFrom = $valueFrom->$key;
                }
            }

            $return = $valueFrom;
        }
        return $return;
    }
}