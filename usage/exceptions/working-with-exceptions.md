# Working with exceptions
The Laravel Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Laravel Cart package or to handle the error based on the type of exceptions. The Laravel Cart packages can throw the following exceptions:

| Exception                    | Reason                                                                              |
| ---------------------------- | ----------------------------------------------------------------------------------- |
| *InvalidArgumentException*   | When you missed or entered invalid data into the required arguments of the methods. |
| *InvalidAssociatedException* | When the associated class of the cart item doesn't exist.                           |
| *InvalidModelException*      | When the associated model of the cart item doesn't exist.                           |
| *InvalidCartNameException*   | When you name the cart or group with a string that is not allowed.                  |
| *InvalidHashException*       | When the hash code information you provided doesn't exist.                          |
| *UnknownCreatorException*    | When you retrieve an instance that does not exist in the cart.                      |
