<?php
require "../lib/Spyc.php";
require "../vendor/autoload.php";

use Aws\Ec2\Ec2Client;
use Aws\Common\Enum\Region;
use Aws\S3\S3Client;
use Aws\AutoScaling\AutoScalingClient;

$opt = getopt("c:");
if(file_exists($opt["c"])){
	$config = spyc_load_file($opt["c"]);
	switch ($config["Platform"]){
		case "EC2":
		performEC2($config);
		break;
		case "S3":
		performS3($config);
		break;
		case "ASG":
		performASG($config);
		break;
	}
} else
	echo "Error: file not found \n";
	
function performS3($config){
	$ak = $config["Keys"]["Access_Key"];
	$sk = $config["Keys"]["Secret_Key"];
		
		$client = S3Client::factory(array("key"=>$ak, "secret"=>$sk));
		
		//First get all buckets
		$res = $client->listBuckets();
		foreach($res["Buckets"] as $bucket){
			//request tag from the bucket
			$request = array("Bucket" => $bucket["Name"]);
			try {
			$bucketTags = $client->getBucketTagging($request);
			} catch (exception $e){ continue;}
			
			//Create/Delete a new tag with the right name 
			foreach($bucketTags["TagSet"] as $tag){
				if($tag["Key"]==$config["Search"]){
					//create
					$request = array("Bucket" => $bucket["Name"],
								"TagSet" => array(array("Key" => $config["Replace"],
														"Value" => $tag["Value"])));
					$client->putBucketTagging($request);
					echo $bucket["Name"].": Adding the new tag ".$config["Replace"]."\n";
					//Delete here once found (I don't think AWS supports specific S3 bucket tag deletes)
				}
			}
		}

}

function performASG($config){
	$ak = $config["Keys"]["Access_Key"];
	$sk = $config["Keys"]["Secret_Key"];
	$region = $config["Region"];

	foreach($region as $reg){
		echo "Searching & Replacing in Region: ".$reg;
		echo "\n ---------------------------------------------------------------------------- \n";
		
		$client = AutoScalingClient::factory(array("key"=>$ak, "secret"=>$sk, "region"=>$reg));
		
		//First create a request for all tags in interest
		$request = array(
					"Filters" => 
						array(
							array("Name" => "key", "Values" => array($config["Search"]))));
		$res = $client->describeTags($request);

		//Now recreate/delete the tag with the right name!
		foreach($res["Tags"] as $tagData){
		$request = array(
					"Tags" => array(array(
						"ResourceId" => $tagData["ResourceId"],
						"ResourceType" => "auto-scaling-group",
						"PropagateAtLaunch" => true,
						"Key" => $config["Replace"],
						"Value" => $tagData["Value"])));
		echo $tagData["ResourceId"].": Replacing ".$config["Search"]." with ".$config["Replace"] . "\n";
		$client->createOrUpdateTags($request);
		
		//say goodbye to the old one!
		$request["Tags"][0]["Key"] = $config["Search"];
		echo $tagData["ResourceId"].": Deleting old key - ".$config["Search"] . "\n";
		$client->deleteTags($request);

		}
		
		echo "\n";
	}
}
	
function performEC2($config){
	$ak = $config["Keys"]["Access_Key"];
	$sk = $config["Keys"]["Secret_Key"];
	$region = $config["Region"];

	foreach($region as $reg){
		echo "Searching & Replacing in Region: ".$reg;
		echo "\n ---------------------------------------------------------------------------- \n";
		
		$client = Ec2Client::factory(array("key"=>$ak, "secret"=>$sk, "region"=>$reg));
		
		//First create a request for all tags in interest
		$request = array(
					"DryRun" => false,
					"Filters" => 
						array(
							array("Name" => "key", "Values" => array($config["Search"]))));
		$res = $client->describeTags($request);

		//Now recreate/delete the tag with the right name!
		foreach($res["Tags"] as $tagData){
		$request = array(
					"DryRun" => false,
					"Resources" => array($tagData["ResourceId"]),
					"Tags" => array(array(
						"Key" => $config["Replace"],
						"Value" => $tagData["Value"])));
		echo $tagData["ResourceId"].": Replacing ".$config["Search"]." with ".$config["Replace"] . "\n";
		$client->createTags($request);
		
		//say goodbye to the old one!
		$request["Tags"][0]["Key"] = $config["Search"];
		echo $tagData["ResourceId"].": Deleting old key - ".$config["Search"] . "\n";
		$client->deleteTags($request);
		}
		
		echo "\n";
	}
}
?>