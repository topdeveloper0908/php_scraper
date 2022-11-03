<?php  
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);    
    class UrlController
    {
        protected function getQueryStringParams()
        {
            parse_str($_SERVER['QUERY_STRING'], $query);
            return $query;
        }

        protected function sendOutput($data, $httpHeaders=array())
        {
            header_remove('Set-Cookie');
    
            if (is_array($httpHeaders) && count($httpHeaders)) {
                foreach ($httpHeaders as $httpHeader) {
                    header($httpHeader);
                }
            }
    
            echo json_encode(array("data" => $data));
            exit;
        }
        
        public function getGtagSend() {
            $strErrorDesc = '';
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $arrQueryStringParams = $this->getQueryStringParams();
            $url = $arrQueryStringParams["url"];
            $conn = mysqli_connect("localhost","root","","scrap");


            if (strtoupper($requestMethod) == 'GET') {
                try {
                    $html = file_get_contents($url);
                    $find = "gtag('event','conversion',{'send_to':";
                    $html = str_replace(" ", "", $html);
                    $html = str_replace("\"", "'", $html);
                    $index = strpos($html,$find);
                    if($index){
                        $newStr = str_replace($find,"",substr($html,$index));
                        $sendToValue = explode("'",$newStr)[1];
                        $date = date("d/m/Y");

                        mysqli_query($conn, "UPDATE urls SET gtag='$sendToValue', update_time='$date' WHERE id = $id");

                        $responseData = ($sendToValue);
                    } else {
                        $responseData = ("Not value");
                    }
                }
                catch (Error $e){
                    $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
                }
            } else {
                $strErrorDesc = 'Method not supported';
                $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            }
            if (!$strErrorDesc) {
                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            } else {
                $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                    array('Content-Type: application/json', $strErrorHeader)
                );
            }
            mysqli_close($conn);
        }

        public function getScreenShort() {
            $strErrorDesc = '';
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $arrQueryStringParams = $this->getQueryStringParams();
            $url = $arrQueryStringParams["url"];
            $id = $arrQueryStringParams["id"];
            $conn = mysqli_connect("localhost","root","","scrap");
            $date = date("d/m/Y");

            if (strtoupper($requestMethod) == 'GET') {
                try {
                    $screen_shot_json_data = file_get_contents("https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&screenshot=true"); 
                    $screen_shot_result = json_decode($screen_shot_json_data, true);
                    $screen_shot = $screen_shot_result['lighthouseResult']['audits']['final-screenshot']['details']['data'];
                    $headers = @get_headers($url);  
                    // Use condition to check the existence of URL
                    if(strpos( $headers[0], '404') || strpos( $headers[0], '500')) {
                        $status = "OFF";
                    }
                    else {
                        $status = "ON";
                    }

                    mysqli_query($conn, "UPDATE urls SET screenshot='$screen_shot', status='$status', update_time='$date' WHERE id = $id");

                    $responseData = $screen_shot;
                }
                catch (Error $e){
                    $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
                }
            } else {
                $strErrorDesc = 'Method not supported';
                $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            }
            if (!$strErrorDesc) {
                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            } else {
                $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                    array('Content-Type: application/json', $strErrorHeader)
                );
            }
            mysqli_close($conn);
        }
    }
?>