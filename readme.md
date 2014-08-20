AWS Tag Mechanic
======

In this third and final installment of Amazon Web Service Tag tools, AWS Tag Mechanic is an automated search and replace tagger that can search for specific tag keys and replace with a different key.  

Requirements
------
  
* PHP >= 5
* AWS SDK - Ensure that AWS SDK folder `/vendor` is present in the same folder.  This can be changed on line 2 of the index.php

Usage
------
  
Using the command line:  
` php index.php -c configFileLocation `  

**-c** - Specify the configuration file location. This is a formatted .YAML file with Amazon keys, and resource information, see section **Configuration File** for more information.  

Configuration File
------
  
The configuration fine is a .YAML format file.  This yaml format is used for easy editing as the markup is in an easy human readable format. [Read more .YAML](http://www.yaml.org/start.html)  

Below is a structure of the configuration file:

__Keys__:

* Secret\_Key \- Your AWS Developer Secret Key  
* Access\_Key \- Your AWS Developer Access Key  

__Platform__: The platform to tag.  Currently, the only possible values are: EC2, ASG and S3. 
__Search__: Tag **key** search term.  This does not replace the value!
__Replace__: Tag **key** replace term. This does not replace the value!
__Region__:  Array of Regions to search in

### Example YAML configuration file

    Keys:
      Secret_Key: ***********************************
      Access_Key: *******************
    Platform: ASG
    Search: Owner
    Replace: Contributor
    Region:
      - us-east-1
      - us-west-1
      - us-west-2
      - eu-west-1


Creator: Kevin Pei  
Copyright 2014  
[MIT License](https://tldrlegal.com/license/mit-license#summary)