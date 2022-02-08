**SETUP**

Create a new singup form at /admin/config/system/sendinblue/sendinblue_signup_form/list

Take note of the id after you created the form:
/admin/config/system/sendinblue/sendinblue_signup_form/1/edit

1 is your ID

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

NOTE: ID is 1 in this case: /admin/config/system/sendinblue/sendinblue_signup_form/1/edit

* **Success Response:**

  * **Code:** 201 <br />
    **Content:** `{
    "status": "success",
    "email": "test@example.com",
    "redirect": "/fr"
    }`

