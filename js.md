```javascript

var C = {
    array:[],
    add:function(id, count, price){
        if(id in this.array ) {
            this.array[id].count+=count;
        }else{
            this.array[id] = {'count':count, 'price':price};
        }
        return true;
    },
    sum:function(){
        var sum = 0;
        this.array.map(function(product) {
            sum = sum + product['count']* product['price'];
        })
        return sum;
    }
}
C.add(2, 2, 2);
C.sum(); // 4
C.add(1, 2, 5);
C.sum(); // 14
```