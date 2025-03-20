# Changelog
All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [2.10.21] - 2025.03.14

### Added

- added function: set curl connection timeout (CURLOPT_CONNECTTIMEOUT)
  - src/szamlaagent/SzamlaAgent.php
    - getRequestConnectTimeout - get curl connection timeout
    - setRequestConnectTimeout - set curl connection timeout
````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    ...
    $agent->setRequestConnectTimeout(10);
````
## [2.10.20] - 2024.11.20

### Fixed

- SzamlaAgentResponse getPdfFileName bugfix for preview mode
  - src/szamlaagent/SzamlaAgentResponse.php 

## [2.10.19] - 2024.10.16

### Changed

- no need fulfillment date for reverse invoice optional
    - szamlaagent/src/szamlaagent/header/InvoiceHeader.php
    - szamlaagent/src/szamlaagent/header/ReverseInvoiceHeader.php

### Added

- added function: set certification file
  - src/szamlaagent/SzamlaAgent.php
    - setCertificationFilePath - set certification file path
    - getCertificationFilePath - get certification file path
- hasCertification - returns whether a certificate file has been set, and checks whether the file exists 
````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    ...
    $agent.setCertificationFilePath("C:\cert.pem");
    ...
    $agent.getCertificationFilePath();
    ...
    $agent.hasCertification();
````
- added function: set data deletion code count for items
  - src/szamlaagent/item/Item.php
    - getDataDeletionCode - get count of data deleteion code count
- setDataDeletionCode - set count of data deleteion code count
````
    item.setDataDeletionCode(3);
    ...
    item.getDataDeletionCode();
````
### Removed

  - src/docs directory


## [2.10.18] - 2024.04.18

### Removed

  - szamlaagent/cert/cacert.pem
  - removed functions: 'setCertificationPath' , 'getCertificationPath', 'getCertificationFile', 'getCertificationFileName'
    - szamlaagent/src/szamlaagent/item/SzamlaAgent.php
  - response header key check change (case insensitive handing)
    - SzamlaAgentResponse.php, InvoiceResponse.php, ReceiptResponse.php

## [2.10.17] - 2023.06.14

### Added

- added new function: receipt item comment
  - szamlaagent/src/szamlaagent/item/ReceiptItem.php
````
    $item = new ReceiptItem();
    ...
    $item->setComment($comment);
    ...
    $item->getComment();
````

## [2.10.16] - 2023.01.25

### Fixed

- date validation check update (PHP 8.2)
  - src/szamlaagent/SzamlaAgentUtil.php

## [2.10.15] - 2022.10.26

### Added

- added: xml saving change, throw an exception in case of error
  - /src/szamlaagent/SzamlaAgentRequest.php
  - /src/szamlaagent/response/SzamlaAgentResponse.php

## [2.10.14] - 2022.09.28

### Removed

Legacy request mode has been removed! If you have used any of the request mode constants (e.g. CALL_METHOD_) 
or the request mode handler function, update your code. The default request mode is CURL 
and you don't need to set it in the constructor now.

- removed function: 'makeLegacyCall', the only request method is CURL
  - src/szamlaagent/SzamlaAgentRequest.php
- removed functions: 'setCallMethod', 'getCallMethod', 'getConnectionModeName'
  - src/szamlaagent/SzamlaAgent.php 

### Added 

- added new function: 'getInvoiceIdentifier' get invoice identifier number from response
  - src/szamlaagent/response/InvoiceResponse.php
````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    ...
    $result = $agent->generateInvoice($invoice);
    ...
    $result->getInvoiceIdentifier();
````

### Changed

- 'downloadPdf(file_name)' change filename of pdf file, default is invoice/receipt number
  - src/szamlaagent/response/SzamlaAgentResponse.php
- Traditional and envelope-friendly template name change
  - src/szamlaagent/document/invoice/Invoice.php
````
    const INVOICE_TEMPLATE_TRADITIONAL = 'SzlaNoEnv';
    const INVOICE_TEMPLATE_ENV_FRIENDLY = 'SzlaAlap';
````




## [2.10.13] - 2022.07.13

### Added

- added new function: 'setCookieHandleMode'  set cookie handle mode (default: file mode)
  - src/szamlaagent/SzamlaAgent.php
  - src/szamlaagent/CookieHandler.php
  - src/szamlaagent/SzamlaAgentRequest.php
````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    ...
    $agent->setCookieHandleMode(SzamlaAgent::COOKIE_HANDLE_MODE_DATABASE);
    ...
    $result->getCookieSessionId();
    ...
    $agent->setCookieSessionId('SESSION_ID');
````

## [2.10.12] - 2022.06.01

### Added

- added new buyer field: 'groupIdentifier'.
    - src/szamlaagent/Buyer.php
    - examples/document/invoice/create_invoice_with_custom_data.php
    
````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    ...
    $buyer = new Buyer('Kovacs Bt.', '2030', 'Érd', 'Tarnoki street 23.');
    ...
    $buyer->setGroupIdentifier('12345678-5-42');
````

## [2.10.11] - 2022.03.02

### Added

- added new function: certification path setting (eg if the default path is changed)
    - src/szamlaagent/SzamlaAgent.php
    
````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    $agent->setCertificationPath('path');
    ...
````

## [2.10.10] - 2022.02.23

### Changed

- default SzamlaAgent request method is CURL.
    - src/szamlaagent/SzamlaaAgent.php

- Log::writeLog method will not throw SzamlaAgentException.
    - src/szamlaagent/Log.php

- xml file save setting changing: if you don't want to save the xml, the API will delete the temporary file created for the request (before PHP v7.4.1). After PHP v7.4.1, it will not create a temporary file (for CURL sending mode).

### Fixed

- check xml file creation when xml generation is turned off (eg if the default path is changed)
    - src/szamlaagent/SzamlaaAgent.php
    - src/szamlaagent/SzamlaaAgentRequest.php

## [2.10.9] - 2021.12.08

### Added

- added new setting field: 'taxNumber' if pay invoice.
    - src/szamlaagent/SzamlaAgentSetting.php
    - examples/document/invoice/pay_invoice.php
    
````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    $agent->getSetting()->setTaxNumber('taxNumber');
    ...
````
   
### Deprecated

- It isn't recommended to use the legacy request mode. Use default CURL mode instead.
    - src/szamlaagent/response/SzamlaAgentRequest.php
    
    ````
        $agent = SzamlaAgentAPI::create('agentApiKey');
        ... 
        $agent->setCallMethod(SzamlaAgentRequest::CALL_METHOD_CURL);
        ...
    ````

## [2.10.8] - 2021.09.01

### Added

- added new function: reverse invoice comment setting
    - examples/document/invoice/create_reverse_invoice.php
    - src/szamlaagent/header/ReverseInvoiceHeader.php
    - src/szamlaagent/SzamlaAgent.php
    
    ````
        $invoice = new ReverseInvoice(Invoice::INVOICE_TYPE_E_INVOICE);
        ... 
        $header->setComment('reverse invoice comment');
        ...
    ````

- added new function: get invoice by external ID
    - src/szamlaagent/SzamlaAgent.php
    - src/szamlaagent/SzamlaAgentSetting.php
    - src/szamlaagent/document/invoice/Invoice.php
    - examples/document/invoice/get_invoice_pdf.php
    
    ````
        $agent = SzamlaAgentAPI::create('agentApiKey');
        ... 
        $result = $agent->getInvoicePdf('TESZT-001', Invoice::FROM_INVOICE_EXTERNAL_ID);
        ...
    ````
    
- added new function: check invoice by external ID

    ````
        $agent = SzamlaAgentAPI::create('agentApiKey');
        ... 
        $agent->isExistsInvoiceByExternalId('invoiceExternalId');
        ...
    ````

## [2.10.7] - 2021.08.25

### Added

- added new function: create final invoice by prepayment invoice number
    - src/szamlaagent/SzamlaAgent.php
    - src/szamlaagent/header/InvoiceHeader.php
    
- added new examples:
    - examples/document/invoice/create_final_invoice_by_invoicenumber.php
    - examples/document/invoice/create_final_invoice_by_ordernumber.php

## [2.10.6] - 2021.07.30

### Removed

- removed own certificate, keychain settings functions (setKeychain, setCertificationFileName)
    - src/szamlaagent/SzamlaAgent.php
    - src/szamlaagent/SzamlaAgentSettings.php
- removed "kulcstartojelszo" field from generated xml

## [2.10.5] - 2021.07.07

### Added

- added new functions: xml file save setting
    - src/szamlaagent/SzamlaAgent.php
    - src/szamlaagent/SzamlaAgentRequest.php
    - src/szamlaagent/SzamlaAgentResponse.php
    - examples/document/invoice/create_invoice_with_custom_data.php

````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    ...
    $agent->setXmlFileSave(true);
    // if you don't want to save the request xml (opcional)
    $agent->setRequestXmlFileSave(false);
    // if you don't want to save the response xml (opcional)
    $agent->setResponseXmlFileSave(false);
````

- added new functions: empty xml, pdf, log directory
    - src/szamlaagent/SzamlaAgentUtil.php
    
````
    SzamlaAgentUtil::emptyXmlDir();
    SzamlaAgentUtil::emptyPdfDir();
    SzamlaAgentUtil::emptyLogDir();
````

## [2.10.4] - 2021.06.30

### Added

- added new header field: 'euVat' (The invoice doesn't include Hungarian VAT. The invoice details will not be sent to the NAV Online invoicing system).
    - src/szamlaagent/header/InvoiceHeader.php
    - examples/document/invoice/create_invoice_with_custom_data.php
    
````
    $agent = SzamlaAgentAPI::create('agentApiKey');
    ...
    $invoice = new Invoice(Invoice::INVOICE_TYPE_E_INVOICE);
    $header = $invoice->getHeader();
    ...
    $header->setEuVat(true);
````
   
## [2.10.3] - 2021.06.23

### Added

- added new function: custom request-timeout setting
    - src/szamlaagent/SzamlaAgent.php (setRequestTimeout)
    - src/szamlaagent/SzamlaAgentRequest.php

````
    $agent = SzamlaAgentAPI::create('agentApiKey');  
    $agent->setRequestTimeout(60);
````
    
## [2.10.2] - 2021.04.14

### Changed

- added 11 new item VAT type
    - src/szamlaagent/item/Item.php

## [2.10.1] - 2021.03.24

### Changed

- we will not send notification email, if the Buyer.sendEmail property is not set
    - src/szamlaagent/Buyer.php
    - src/szamlaagent/BuyerLedger.php
    
- The last two parameters of the InvoiceItemLedger constructor can also be numbers
    - src/szamlaagent/ledger/InvoiceItemLedger.php
    
````
    $itemLedger = new InvoiceItemLedger('economic event type', 'vat economic event type', 123, 467);
````

## [2.10.0] - 2021.03.10

### Added

- added changelog to the package
    - src/szamlaagent/changelog.md 

## [2.9.10] - 2021.01.27

### Added

- added new function: invoice PDF preview creation
    - src/szamlaagent/header/InvoiceHeader.php

````
    $invoice = new Invoice(Invoice::INVOICE_TYPE_E_INVOICE);  
    $invoice->getHeader()->setPreviewPdf(true);
````

- added new function: Turn off default PDF file saving (you can save the preview PDF yourself)
    - src/szamlaagent/SzamlaAgent.php

````
    $agent = SzamlaAgentAPI::create('agentApiKey');  
    $agent->setPdfFileSave(false);
````

### Changed

- TaxPayer interface update (NAV 3.0 format). If you are building business logic on this interface, you recommend using your own XML processor to handle the data coming from NAV.
    - src/szamlaagent/response/TaxPayerResponse.php
    - src/szamlaagent/response/SzamlaAgentResponse.php

### Deprecated

- Processing of data received via the NAV TaxPayer interface via the TaxPayerResponse object was supported until version 2.9.10.
    - src/szamlaagent/response/TaxPayerResponse.php

## [2.9.9] - 2020.12.10

### Added

- added new VAT codes (TAHK, TEHK) 
    - src/szamlaagent/item/Item.php

### Changed

- XML creation logging update (if the creation fails, the generated XML is logged)
    - src/szamlaagent/SzamlaAgentRequest.php
    - src/szamlaagent/SzamlaAgentException.php

## [2.9.8] - 2020.11.24

### Fixed

- TaxPayer interface bugfix (& character handing in taxpayerName field)
    - src/szamlaagent/SimpleXMLExtended.php (cleanXMLNode)
    
## [2.9.7] - 2020.10.07

### Added

- added new function: corrective and reverse invoice buyer taxnumber setting
    - src/szamlaagent/Buyer.php
    
## [2.9.6] - 2020.09.16

### Changed

- Taxpayer options changed
    - src/szamlaagent/TaxPayer.php
    
- The name of the generated XML file contains the full timestamp
    - src/szamlaagent/SzamlaAgentUtil.php (getDateTimeWithMilliseconds) 

### Deprecated

- Taxpayer options changed (Don't use the following values, use this instead: TaxPayer::TAXPAYER_HAS_TAXNUMBER)
    - src/szamlaagent/TaxPayer.php

````
    const TAXPAYER_JOINT_VENTURE = 5;
    const TAXPAYER_INDIVIDUAL_BUSINESS = 4;
    const TAXPAYER_PRIVATE_INDIVIDUAL_WITH_TAXNUMBER = 3;
    const TAXPAYER_OTHER_ORGANIZATION_WITH_TAXNUMBER = 2;
    const TAXPAYER_PRIVATE_INDIVIDUAL = -2;
    const TAXPAYER_OTHER_ORGANIZATION_WITHOUT_TAXNUMBER = -3;
````

## [2.9.5] - 2020.07.15

### Added

- added new TaxPayer interface options (added 6 and 7 value)
    - src/szamlaagent/TaxPayer.php
    
````
    const TAXPAYER_NON_EU_ENTERPRISE = 7;
    const TAXPAYER_EU_ENTERPRISE = 6;
````

- Automatic cookie creation for billing accounts per API key (creates a completely unique cookie based on agent key).
    - src/szamlaagent/SzamlaAgent.php (buildCookieFileName)
    
## [2.9.4] - 2020.07.08

### Added

- added new TaxPayer tag
    - src/szamlaagent/Buyer.php
    
## [2.9.3] - 2020.06.24

### Added

- added new function: custom NAV errorcodes handing (TaxPayer interface - query and convert to JSON)
    - src/szamlaagent/SzamlaAgentUtil.php
    
````
    $data = SzamlaAgentAPI::create('agentApiKey')->getTaxPayer('12345678')->toJson();
    $data = SzamlaAgentAPI::create('agentApiKey')->getTaxPayer('12345678')->getDataObj();
````

### Fixed

- taxpayerData property duplication bugfix (TaxPayer interface)
- SimpleXMLExtended XML parser nullpointer fix
    - src/szamlaagent/SimpleXMLExtended.php (removeChild)

## [2.9.2] - 2020.06.19

### Changed

- update TaxPayer interface (custom namespace handing)
    - src/szamlaagent/SzamlaAgentUtil.php
    - src/szamlaagent/SimpleXMLExtended.php
    - src/szamlaagent/response/SzamlaAgentResponse.php

## [2.9.1] - 2020.06.17

### Added

- added new function: custom request-header setting
    - src/szamlaagent/SzamlaAgent.php (addCustomHTTPHeader)
    - src/szamlaagent/SzamlaAgentRequest.php

## [2.9.0] - 2020.06.10

### Added

- added new function: custom invoice template setting (retro, 8 cm, traditional, etc.)
    - src/szamlaagent/header/InvoiceHeader.php
    - src/szamlaagent/header/ReverseInvoiceHeader.php
    - src/szamlaagent/document/invoice/Invoice.php
    - src/szamlaagent/response/InvoiceResponse.php
    - src/szamlaagent/response/ReceiptResponse.php
    - src/szamlaagent/response/SzamlaAgentResponse.php
    - src/szamlaagent/response/ProformaDeletionResponse.php
    
````
    $invoice->getHeader()->setInvoiceTemplate(Invoice::INVOICE_TEMPLATE_8CM);
````

- added new function: failed invoice notification sending handling
    - src/szamlaagent/response/SzamlaAgentResponse.php
  
````
    // successful response handing
    if (​ $result->isSuccess()​ ) {
        echo 'A számla sikeresen elkészült. Számlaszám: ' . $result->getDocumentNumber();
    }
    // if notification sending fails, we can handle it
    if (​ $result->hasInvoiceNotificationSendError()​ ) {
        var_dump($result->getDataObj());
    }
````

## [2.8.4] - 2020.05.21

### Added

- added new function: certification filename setting
   - src/szamlaagent/SzamlaAgent.php (setCertificationFileName)

### Changed

- changed TaxPayer interface (NAV data structure changed)
    - src/szamlaagent/response/TaxPayerResponse.php

## [2.8.3] - 2020.05.06

### Changed

- setting a default path for generated files (xmls, pdf, logs, attachments)
    - src/szamlaagent/SzamlaAgentUtil.php (setBasePath)
- the request connection function has been optimized
    - src/szamlaagent/SzamlaAgentRequest.php (checkConnection)
    
### Removed

- removed function: setting request calling type from session (PHP v1.0)
    - src/szamlaagent/SzamlaAgentRequest.php

## [2.8.2] - 2019.12.26

### Added

- added new function: setting logging path and filename
    - src/szamlaagent/Log.php
    - src/szamlaagent/SzamlaAgent.php
    
## [2.8.1] - 2019.11.20

### Fixed

- fixed cookie management bug (custom cookie filename setting)
    - src/szamlaagent/SzamlaAgentRequest.php
    
## [2.8.0] - 2019.10.24

### Added

- added new functions: buyer ledger invoices settlement period
    - src/szamlaagent/BuyerLedger.php
    
## [2.7.0] - 2019.09.04

### Added

- added new function: attach files to an invoice (maximum 5)
    - src/szamlaagent/SzamlaAgent.php
    - src/szamlaagent/SzamlaAgentUtil.php
    - src/szamlaagent/SzamlaAgentRequest.php
    - src/szamlaagent/SzamlaAgentException.php
    - src/szamlaagent/document/invoice/Invoice.php
    - examples/document/invoice/create_invoice_with_attachment.php
    
## [2.6.0] - 2019.08.28

### Added

- added new interfaces: "getInvoiceData" and "getReceiptData" (xmlszamlaxml XML interface update)
    - examples/document/invoice/get_invoice_data.php
    - examples/document/invoice/get_invoice_pdf.php
- Nyugta adatok lekérdezése
    - examples/document/receipt/get_receipt_data.php
    
## [2.5.0] - 2019.07.25

### Added

- added SzamlaAgent API key interface
    - src/szamlaagent/SzamlaAgent.php
    - src/szamlaagent/SzamlaAgentAPI.php
    - src/szamlaagent/SzamlaAgentRequest.php
    - src/szamlaagent/SzamlaAgentSetting.php
    - src/szamlaagent/document/invoice/Invoice.php
    
## [2.4.0] - 2019.07.25

### Changed

- changed autoload function (import only from 'SzamlaAgent' namespace)
  - examples/autoload.php
  - src/szamlaagent/SzamlaAgent.php
  - examples/document/receipt/create_receipt_with_default_data.php
  - examples/document/receipt/create_receipt_with_custom_data.php
  
- logging optimized (easier search)
  - src/szamlaagent/Log.php

- changed the invoice response customer account url decoding
  - src/szamlaagent/response/InvoiceResponse.php
  
### Fixed

- fixed documentation bug (cookie management)

## [2.3.0] - 2019.06.26

### Changed

- changed exception handling
  - src/szamlaagent/response/SzamlaAgentResponse.php
  
## [2.2.0] - 2019.06.05

### Added

- added new function: create proforma invoice with ordernumber
  - src/szamlaagent/header/InvoiceHeader.php
  
## [2.1.0] - 2019.05.21

### Added

- added PHP API logging
  - src/szamlaagent/SzamlaAgent.php
   
## [2.0.0] - 2019.03.20

### Added

- PHP API package created