# Landofcoder Magento 2 Seller Product List
Support filter products list of seller

## Core Features
- Module supports you to create a wanted list of products based on attribute, types, conditions of items.
To create a rule list is very simple. Also, this module is easy to configure too.
- The module just support REST API features.
- Support GraphQl

## Install Extension

- Require Setup [Magento 2 Multi-Vendor Module](https://landofcoder.com/magento-2-marketplace-extension.html/)

- Run command setup module via composer:

```
composer require landofcoder/module-seller-product-list
php bin/magento module:enable Lofmp_Productlist
php bin/magento setup:upgrade

```

## API docs

Response Data Success: 
```
{
    "items": [
        {
            "id": 0,
            "sku": "string",
            "name": "string",
            "attribute_set_id": 0,
            "price": 0,
            "status": 0,
            "visibility": 0
            ...
        }
    ],
    "search_criteria": {
        "filter_groups": [
        {
            "filters": [
            {
                "field": "string",
                "value": "string",
                "condition_type": "string"
            }
            ]
        }
        ],
        "sort_orders": [
        {
            "field": "string",
            "direction": "string"
        }
        ],
        "page_size": 0,
        "current_page": 0
    },
    "total_count": 0
}
```

Error data response:
```
{
  "message": "string",
  "errors": [
    {
      "message": "string",
      "parameters": [
        {
          "resources": "string",
          "fieldName": "string",
          "fieldValue": "string"
        }
      ]
    }
  ],
  "code": 0,
  "parameters": [
    {
      "resources": "string",
      "fieldName": "string",
      "fieldValue": "string"
    }
  ],
  "trace": "string"
}
```

### API Endpoints

{sellerUrl} - is public seller url key. Example: seller-a

1. New Arrival Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/newarrival``

2. Latest Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/latest``

3. Best Seller Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/bestseller``

4. Random Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/random``

5. Top Rated Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/toprated``

6. Specials Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/specials``

Note: Require use current store code for endpoint.
Example: ``http://[your_domain]/rest/default/V1/seller-productlist/{sellerUrl}/specials``

default - is default store view code

7. Most Viewed Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/mostviewed``

8. Featured Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/featured``

9. Deals Products

``[Your Domain]/V1/seller-productlist/{sellerUrl}/deals``

Note: Require use current store code for endpoint.
Example: ``http://[your_domain]/rest/default/V1/seller-productlist/{sellerUrl}/deals``
default - is default store view code


## Donation

If this project help you reduce time to develop, you can give me a cup of coffee :) 

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/paypalme/allorderdesk)


**Our Magento 2 Extensions List**

* [Megamenu for Magento 2](https://landofcoder.com/magento-2-mega-menu-pro.html/)

* [Page Builder for Magento 2](https://landofcoder.com/magento-2-page-builder.html/)

* [Magento 2 Marketplace - Multi Vendor Extension](https://landofcoder.com/magento-2-marketplace-extension.html/)

* [Magento 2 Multi Vendor Mobile App Builder](https://landofcoder.com/magento-2-multi-vendor-mobile-app.html/)

* [Magento 2 Form Builder](https://landofcoder.com/magento-2-form-builder.html/)

* [Magento 2 Reward Points](https://landofcoder.com/magento-2-reward-points.html/)

* [Magento 2 Flash Sales - Private Sales](https://landofcoder.com/magento-2-flash-sale.html)

* [Magento 2 B2B Packages](https://landofcoder.com/magento-2-b2b-extension-package.html)


**Featured Magento Services**

* [Hire Magento 2 Development Team](https://landofcoder.com/magento-2-create-online-store/)

* [Dedicated Magento 2 Developer](https://landofcoder.com/magento-support-ticket.html/)

* [Magento 2 Multi Vendor Development](https://landofcoder.com/magento-2-create-marketplace/)

* [Magento Website Maintenance Service](https://landofcoder.com/magento-2-customization-service/)

* [Magento Professional Installation Service](https://landofcoder.com/magento-2-installation-service.html)

* [Customization Service](https://landofcoder.com/magento-customization-service.html)
