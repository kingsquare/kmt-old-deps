READ ME
=========
This vendor package has been altered.

Note: we've tried upgrading to 3.3.3 but this gave errors with (at least) sending a testmail due to missing MIME parts
	If considering upgrading this library use the current (4+ rewritten) version

Changes:
- in Swift.php: split has been deprecated in favor of preg_split
- in Encoder.php: utf8 checking
- in Headers.php: PHP5.5 deprecation of /e modifier
- in Swift/Plugin/FileEmbedder.php and Swift/File.php: added checks for (get/set)_magic_quotes_runtime

the changes are fairly simple:
--- Swift.php
+++ New_Swift.php
@@ -277 +277 @@
-      $attributes = split("[ =]", $extension);
+      $attributes = preg_split('![ =]!', $extension);

--- Swift/Message/Encoder.php
+++ Swift/Message/New_Encoder.php
@@ -373,15 +373,7 @@
    */
   public function isUTF8($data)
   {
-    return preg_match('%(?:
-    [\xC2-\xDF][\x80-\xBF]				# non-overlong 2-byte
-    |\xE0[\xA0-\xBF][\x80-\xBF]			# excluding overlongs
-    |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
-    |\xED[\x80-\x9F][\x80-\xBF]			# excluding surrogates
-    |\xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
-    |[\xF1-\xF3][\x80-\xBF]{3}			# planes 4-15
-    |\xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
-    )+%xs', $data);
+	  return (mb_detect_encoding($data, 'UTF-8', true) == 'UTF-8');
   }
   /**
    * This function checks for 7bit *printable* characters

--- Swift/Message/Headers.php
+++ Swift/Message/Headers.php
@@ -420,8 +420,10 @@

       if (false !== $p = strpos($encoded_value[$key], $this->LE))
       {
-        $cb = 'str_replace("' . $this->LE . '", "", "<$1>");';
-        $encoded_value[$key] = preg_replace("/<([^>]+)>/e", $cb, $encoded_value[$key]);
+		$le = $this->LE;
+        $encoded_value[$key] = preg_replace_callback("/<([^>]+)>/", function ($matches) use ($le) {
+			return str_replace($le, '', '<' . $matches[1] . '>');
+		}, $encoded_value[$key]);
       }

       //Turn our header into an array of lines ready for wrapping around the encoding specification

--- Swift/File.php
+++ Swift/File.php
@@ -133,16 +133,15 @@
    */
   public function readln()
   {
-    set_magic_quotes_runtime(0);
+    if ($this->magic_quotes) set_magic_quotes_runtime(0);
     $this->createHandle();
     if (!$this->EOF())
     {
       $ret = fgets($this->handle);
     }
     else $ret = false;
-
+
-    set_magic_quotes_runtime($this->magic_quotes);
-
+    if ($this->magic_quotes) set_magic_quotes_runtime($this->magic_quotes);
     return $ret;
   }
   /**
@@ -153,9 +152,9 @@
   public function readFull()
   {
     $ret = "";
-    set_magic_quotes_runtime(0);
+    if ($this->magic_quotes) set_magic_quotes_runtime(0);
     while (false !== $chunk = $this->read(8192, false)) $ret .= $chunk;
-    set_magic_quotes_runtime($this->magic_quotes);
+    if ($this->magic_quotes) set_magic_quotes_runtime($this->magic_quotes);
     return $ret;
   }
   /**
@@ -166,15 +165,15 @@
    */
   public function read($bytes, $unquote=true)
   {
-    if ($unquote) set_magic_quotes_runtime(0);
+    if ($unquote && $this->magic_quotes) set_magic_quotes_runtime(0);
     $this->createHandle();
     if (!$this->EOF())
     {
       $ret = fread($this->handle, $bytes);
     }
     else $ret = false;
-
+
-    if ($unquote) set_magic_quotes_runtime($this->magic_quotes);
+    if ($unquote && $this->magic_quotes) set_magic_quotes_runtime($this->magic_quotes);

     return $ret;
   }
--- Swift/Plugin/FileEmbedder.php
+++ Swift/Plugin/FileEmbedder.php
@@ -337,9 +337,9 @@
       return $matches[1] . $cid . $matches[4];
     }
     $magic_quotes = get_magic_quotes_runtime();
-    set_magic_quotes_runtime(0);
+    if ($magic_quotes) set_magic_quotes_runtime(0);
     $filedata = @file_get_contents($url);
-    set_magic_quotes_runtime($magic_quotes);
+    if ($magic_quotes) set_magic_quotes_runtime($magic_quotes);
     if (!$filedata)
     {
       return $matches[1] . $matches[3] . $matches[4];