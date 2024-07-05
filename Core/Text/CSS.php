<?php

declare(strict_types=1);

namespace Core\Text;

class CSS{

    private $fileNames = [];

    function __construct($fileNames = []){
        $this->fileNames = $fileNames;
    }

    private function fileValidator($fileName){
		
		$url = str_replace(' ', '%20', $fileName);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
		curl_close($ch);
		
		// check if fileName is URL then verify first using CURL
		if($httpCode>=200 && $httpCode<300)
		{  
			return true; 
		} 
		else 
		{ 
			$fileParts = explode('.',$fileName);
			$fileExtension  = end($fileParts);

			if(strtolower($fileExtension) !== "css"){
				throw new \Exception("Invalid file type. The extension for the file $fileName is $fileExtension.");
			}

			if(!file_exists($fileName)){
				throw new \Exception("The given file $fileName does not exists.");
			}
		}

        

    }

    private function setHeaders(){
        header('Content-Type: text/css');
    }

    public function minify(){

        $this->setHeaders();

        $minifiedCss = "";
        $fileNames = $this->fileNames;

        foreach ($fileNames as $fileName){
            try{
                $this->fileValidator($fileName);
                $fileContent = file_get_contents($fileName);
                $minifiedCss = $minifiedCss . $this->minifyCSS($fileContent);
            } catch(\Exception $e) {
                echo 'Message: ' .$e->getMessage();
                return false;
            }
        }

        return $minifiedCss;

    }

    //Credits for minifyCSS @ https://gist.github.com/Rodrigo54/93169db48194d470188f
    public function minifyCSS($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
            ),
            $input);
    }


}
