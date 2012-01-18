<?php

namespace Ebutik\MongoSessionBundle\Interfaces;

interface SessionEmbeddable
{
  /* 
   This interface doesn't contain any required methods.
   The requirement you should adhere to is that the class
   is an embedded document.

   PLEASE NOTE! As long as MODM-160 remains unresolved you
   also need to have an id on the embeddable object, as well as
   embedded children.
  */
}