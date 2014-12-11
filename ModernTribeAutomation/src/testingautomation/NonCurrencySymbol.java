package testingautomation;

import java.io.File;
import java.io.IOException;

import org.apache.commons.io.FileUtils;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.WebElement;
import org.testng.annotations.Test;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;


public class NonCurrencySymbol extends SetUpBase{
	 
			//username
			@Test
			public void addCost() throws InterruptedException {


				//logging into the admin app

				//username

				driver.findElement(By.id("user_login")).sendKeys("gabe");

				//password

				driver.findElement(By.id("user_pass")).sendKeys("testprojecttestaccess");

				//clicking submit button

				driver.findElement(By.id("wp-submit")).click();
				
				//clicking menu to event
				
				WebElement element = (new WebDriverWait(driver, 4))
						.until(ExpectedConditions.elementToBeClickable(By.id("menu-posts-tribe_events")));
						
						element.click();
				//WebElement event=driver.findElement(By.xpath("//*[@id='menu-posts-tribe_events']/a/div[3]"));
			    //event.click();
			    
			    
			    //adding a new event
			   WebElement addNewEvent=driver.findElement(By.xpath("/html/body/div/div[3]/div[2]/div[1]/div[5]/h2/a[1]"));
			    addNewEvent.click();
			    
			   
		    
			    //Adding a title name
			    WebElement titleName=driver.findElement(By.id("title"));
			    
			   
			   titleName.click(); 
			    Thread.sleep(3);
			   //title name 
			    titleName.sendKeys("Adding cost");
			    

			    
			    
			    
			    //Entering an End Date
				
				WebElement endDate=driver.findElement(By.id("EventEndDate"));
			    //clearing endDate
			    endDate.clear();
			    
			    //entering date
			    endDate.sendKeys("2014-11-12");
			    
			    
			    WebElement endDone =driver.findElement(By.xpath("//*[@id='ui-datepicker-div']/div[5]/button[2]"));
				   endDone.click();
			  
				 
				  //entering non-currency symbols
				   
				   driver.findElement(By.id("EventCost")).sendKeys("..%%%55");
				  


			    driver.findElement(By.id("publish")).sendKeys(Keys.ENTER);

			    driver.findElement(By.xpath("//*[@id='message']/p/a")).click();;
			    
			    for (String winHandle : driver.getWindowHandles()) {
			      driver.switchTo().window(winHandle); // switch focus of WebDriver to the next found window handle (that's your newly opened window)
			    }

			    
				WebElement date = (new WebDriverWait(driver, 4))
						.until(ExpectedConditions.elementToBeClickable(By.id("tribe-events-content")));
						
					String content=	date.getText();
			   
			    
		  

		System.out.print(content);

		assert content.contains("..%%%55");
		

File scrNoncurrency = ((TakesScreenshot) driver)
.getScreenshotAs(OutputType.FILE);

try {
FileUtils.copyFile(scrNoncurrency, new File(
	"/Users/gholmes/Documents/checkNonCurrencySymbol"));
} catch (IOException e) {
// TODO Auto-generated catch block
e.printStackTrace();
}

	}

	}
	

