**API**
----
Create a new subscriber

* **URL**

  /fr/_sendinblue

* **Method:**

  `POST`

*  **URL Params**

   **Required:**

   `id=[integer]`
   `email=[string]`


* **Success Response:**

  * **Code:** 201 <br />
    **Content:** `{
    "status": "success",
    "email": "test@example.com",
    "redirect": "/fr"
    }`

