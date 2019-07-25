mPDF Writer for Commerce
==

Introduction
--
mPDF Writer is an optional module for the Commerce platform by Modmore https://www.modmore.com which runs on the MODX Content Management System.
mPDF Writer adds a PDF output option that Commerce can use to generate PDFs (like invoices), based on the [mPDF 8.x library](https://mpdf.github.io/).

Before the creation of this module, the only other existing option to generate PDF invoices within Commerce was the PDFCrowd Writer (https://www.modmore.com/commerce/extensions/pdfcrowd-writer/) which is excellent but requires an account to access the PDFCrowd API.
mPDF allows for free PDF generation under the GPL2.0 license.

Requirements
--
mPDF Writer requires [Commerce 1.0 or above](https://www.modmore.com/commerce/).

Usage
--
1. Download and install the package from our package provider.
2. In the Commerce Dashboard, navigate to Configuration > Modules. Find the Commerce_mPDFWriter module, and enable it.
3. Once enabled, Commerce will automatically use mPDF Writer to generate invoices.
