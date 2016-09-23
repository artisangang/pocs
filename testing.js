      function testing() {

         var x = Math.floor((Math.random() * 4000) + 2000);

         setTimeout(function () {

           
           var s = Math.floor((Math.random() * 60) + 1);
           var e = Math.floor((Math.random() * 70) + 40);
           var text = "Lorem ipsum odio ornare placerat risus tempor, nisi purus venenatis ad fringilla porttitor adipiscing, netus curabitur dui odio nullam.".substring(s,e);
           send(text);
           testing();

         }, x);

         
    }


     // test 

                setTimeout(function () {
                    testing();
                }, 10000);

                 setTimeout(function () {
                    testing();
                }, 10000);

                setInterval(function () {

                    console.log('Total Sent:' + totalSent);
                    console.log('Total Received:' + totalReceived);

                }, 60000);