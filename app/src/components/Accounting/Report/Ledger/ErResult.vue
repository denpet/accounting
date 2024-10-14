<template>aa</template>
<script></script>
<!--h1 class="main">Accounting</h1>
<form
  class="accounting"
  action="{url controller='accounting' action='balance-sheet'}"
  method="post"
>
	<table>
		<tr>
			<td>Date</td>
			<td><input class='date' type='text' name="date" value="{$report.date}" onclick="displayDatePicker('date', this, 'ymd', '-'); return false;" /></td>
		</tr>
		<tr>
			<td><input type='submit' name='submit' value='Refresh' /></td>
			<td><input type='submit' name='previous' value='<<' /> <input type='submit' name='next' value='>>' /></td>
		</tr>
	</table>
</form>
<table style="border: 1px solid black" width="100%">
    <tr class='sort_head'>
		<th colspan=2 width="45%">Assets</th>
		<th width='10%' />
		<th colspan=2 width="45%">Liabilities/Equity</th>
	</tr>
    <tr class='sort_head'>
		<th>Name</th>
		<th>Balance</th>
		<th/>
		<th>Name</th>
		<th>Balance</th>
	</tr>
	{foreach from=$accounts item=account}
        <tr class="{cycle values='sort_odd,sort_even'}">
			<td>{$account.a.name}</td>
			<td align='right'>{if $account.a.type!=''}{$account.a.balance|number}{/if}</td>
			<td />
			<td>{$account.le.name}</td>
			<td align='right'>{if $account.le.type!=''}{$account.le.balance|number}{/if}</td>
		</tr>
	{/foreach}	
</table-->
