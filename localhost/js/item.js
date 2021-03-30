function dishes_tbl(count, dishes_tittle, dishes_price, dishes_img, dish_id)
{

	var t = document.getElementsByClassName("catalog-list")[0];
	var s = "";
	var k = Math.ceil(count/4);
	var c = 1;
	var dt_str = dishes_tittle.split(',');
	var dp_str = dishes_price.split(' ');
	var di_str = dishes_img.split(' ');
	var di_id = dish_id.split(',');
	

	for (var i = 0; i < k; i++)
	{
		s += "<tr>";
		for (var j = 0; j < 4; j++)
		{
			console.log(di_id[c]);
			s += '<td><div class="product-item"><img src="'+ di_str[c] +'"><div class="product-list"><h3>'+ dt_str[c] +'</h3><span class="price">'+ dp_str[c] +' ₽ </span><a class="button" href="cart.php?page=cart&action=add&id='+ di_id[c] + '">В корзину</a></div></div></td>';
			c++;
		}
		s += "</tr>";

	}
	
	t.innerHTML = s;
	
}
