# Customer CC attribute field #

Comma seperated attribute field within customer account area to add cc to invoice ONLY emails.

# Install instructions # 

`composer require dominicwatts/emailcc`

`php bin/magento setup:upgrade`

# Usage instructions # 

Customer has ability to set CC address in customer account area.  
This can either be a single address or a list of comma seperated addresses.

![Screenshot](https://i.snag.gy/FjQmN4.jpg)

Once configured invoice ONLY email will be sent to main recipient and CC recipient(s)

In admin area, in Invoice view, Send Email button allows override of invoice email