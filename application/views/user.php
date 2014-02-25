<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="docs">
	<h1><a href="<?php echo base_url(); ?>tutorials/docs/">&larr; Go back</a></h1>
	<div class="docs_content">
		<h2>How to access the user object</h2>
		<div class="docs_text">
			 <code>$this->user-><span style="color:green;">method()</span>;</code>
		</div>
	</div><div class="docs_content">
							<h2>cookieLogIn()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Int] </span>setUserDetails($username, $sha_pass_hash)</h2>
							<div class="docs_text">
								 When they log in this should be called to set all the user details.

								 <div class="param"> String $username </div><div class="param"> String $sha_pass_hash </div>
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[String hashed password] </span>createHash([$username, $password])</h2>
							<div class="docs_text">
								 Creates a hash of the password we enter

								 <div class="param">(Optional) String $username </div><div class="param">(Optional) String $password plain text
 </div>
							</div>
						</div><div class="docs_content">
							<h2>is_logged_in()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>is_not_logged_in()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>isOnline()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Boolean] </span>requireRank($requiredRank [, $die])</h2>
							<div class="docs_text">
								 Check if the user has permission to do a certain task

								 <div class="param"> Int $requiredRank ID column
 </div><div class="param">(Optional) Boolean $die </div>
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Boolean] </span>isStaff([$id])</h2>
							<div class="docs_text">
								 Check if the user rank has any staff permissions

								 <div class="param">(Optional)  $id </div>
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Boolean] </span>isGm()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Boolean] </span>isDev()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Boolean] </span>isAdmin()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Boolean] </span>isOwner()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Int] </span>getRank([$userId])</h2>
							<div class="docs_text">
								 Get the CMS user rank (ranks table)

								 <div class="param">(Optional) Int $userId </div>
							</div>
						</div><div class="docs_content">
							<h2>getUserData()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[String] </span>getUserGroup()</h2>
							<div class="docs_text">
								 Get the user group name

								 
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[String] </span>getAccountStatus([$id])</h2>
							<div class="docs_text">
								 Check if the account is banned or active

								 <div class="param">(Optional)  $id </div>
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[String] </span>getNickname([$id])</h2>
							<div class="docs_text">
								 Get the nickname

								 <div class="param">(Optional) Int $id </div>
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[String] </span>getAvatar([$id, $size])</h2>
							<div class="docs_text">
								 Get the user's avatar

								 <div class="param">(Optional) Int $id </div><div class="param">(Optional) String $size </div>
							</div>
						</div><div class="docs_content">
							<h2><span style='color:green;'>[Array] </span>getCharacters($userId [, $realmId])</h2>
							<div class="docs_text">
								 get the user it's characters, returns array with realmnames and character names and character id when specified realm is -1 or the default

								 <div class="param"> int $userId </div><div class="param">(Optional) int $realmId </div>
							</div>
						</div><div class="docs_content">
							<h2>getId([$username])</h2>
							<div class="docs_text">
								 
								 <div class="param">(Optional)  $username </div>
							</div>
						</div><div class="docs_content">
							<h2>getUsername([$id])</h2>
							<div class="docs_text">
								 
								 <div class="param">(Optional)  $id </div>
							</div>
						</div><div class="docs_content">
							<h2>getPassword()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>getEmail()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>getExpansion()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>getOnline()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>getRegisterDate()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>getVp()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>getDp()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>getLastIP()</h2>
							<div class="docs_text">
								 
								 
							</div>
						</div><div class="docs_content">
							<h2>setUsername($newUsername)</h2>
							<div class="docs_text">
								 
								 <div class="param">  $newUsername </div>
							</div>
						</div><div class="docs_content">
							<h2>setPassword($newPassword)</h2>
							<div class="docs_text">
								 
								 <div class="param">  $newPassword </div>
							</div>
						</div><div class="docs_content">
							<h2>setEmail($newEmail)</h2>
							<div class="docs_text">
								 
								 <div class="param">  $newEmail </div>
							</div>
						</div><div class="docs_content">
							<h2>setExpansion($newExpansion)</h2>
							<div class="docs_text">
								 
								 <div class="param">  $newExpansion </div>
							</div>
						</div><div class="docs_content">
							<h2>setRank($newRank)</h2>
							<div class="docs_text">
								 
								 <div class="param">  $newRank </div>
							</div>
						</div><div class="docs_content">
							<h2>setVp($newVp)</h2>
							<div class="docs_text">
								 
								 <div class="param">  $newVp </div>
							</div>
						</div><div class="docs_content">
							<h2>setDp($newDp)</h2>
							<div class="docs_text">
								 
								 <div class="param">  $newDp </div>
							</div>
						</div></aside>

<div class="clear"></div>